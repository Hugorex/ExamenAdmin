<?php
/**
 * Plugin Name: WP LDAP User Sync
 * Description: Sincroniza usuarios de WordPress con LDAP para acceso a webmail
 * Version: 1.0
 * Author: Auto-generated
 */

// Hook cuando se crea un nuevo usuario en WordPress
add_action('user_register', 'sync_user_to_ldap', 10, 1);

function sync_user_to_ldap($user_id) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return;
    }
    
    // Configuración LDAP
    $ldap_host = '192.168.56.14';
    $ldap_port = 389;
    $ldap_admin_dn = 'cn=admin,dc=patitohosting,dc=licic';
    $ldap_admin_pass = 'ldapadmin123';
    $ldap_base_dn = 'ou=people,dc=patitohosting,dc=licic';
    
    // Conectar a LDAP
    $ldap_conn = ldap_connect($ldap_host, $ldap_port);
    if (!$ldap_conn) {
        error_log("WP-LDAP: No se pudo conectar a LDAP");
        return;
    }
    
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    
    // Autenticar como admin
    if (!@ldap_bind($ldap_conn, $ldap_admin_dn, $ldap_admin_pass)) {
        error_log("WP-LDAP: Fallo autenticación admin LDAP");
        ldap_close($ldap_conn);
        return;
    }
    
    // Buscar el UID más alto para asignar uno nuevo
    $search = ldap_search($ldap_conn, $ldap_base_dn, '(objectClass=posixAccount)', ['uidNumber']);
    $entries = ldap_get_entries($ldap_conn, $search);
    
    $max_uid = 10000;
    for ($i = 0; $i < $entries['count']; $i++) {
        if (isset($entries[$i]['uidnumber'][0])) {
            $uid_num = intval($entries[$i]['uidnumber'][0]);
            if ($uid_num > $max_uid) {
                $max_uid = $uid_num;
            }
        }
    }
    $new_uid = $max_uid + 1;
    
    // Preparar datos del usuario
    $username = $user->user_login;
    $email = $user->user_email;
    $display_name = !empty($user->display_name) ? $user->display_name : $username;
    
    // Generar hash SSHA de la contraseña (se usa la misma que en WordPress)
    // Como no tenemos acceso a la contraseña en texto plano después del registro,
    // usamos una contraseña temporal que el usuario debe cambiar
    $temp_password = 'password123';
    $password_hash = generate_ssha_password($temp_password);
    
    // Verificar si el usuario ya existe
    $user_dn = "uid=$username,$ldap_base_dn";
    $existing = @ldap_read($ldap_conn, $user_dn, '(objectClass=*)', ['dn']);
    
    if ($existing) {
        error_log("WP-LDAP: Usuario $username ya existe en LDAP");
        ldap_close($ldap_conn);
        return;
    }
    
    // Crear entrada LDAP
    $entry = array(
        'objectClass' => array('inetOrgPerson', 'posixAccount', 'shadowAccount'),
        'uid' => $username,
        'sn' => $display_name,
        'givenName' => $display_name,
        'cn' => $display_name,
        'displayName' => $display_name,
        'uidNumber' => $new_uid,
        'gidNumber' => $new_uid,
        'userPassword' => $password_hash,
        'gecos' => $display_name,
        'loginShell' => '/bin/bash',
        'homeDirectory' => "/home/$username",
        'mail' => $email
    );
    
    // Agregar usuario a LDAP
    if (@ldap_add($ldap_conn, $user_dn, $entry)) {
        error_log("WP-LDAP: Usuario $username sincronizado exitosamente con email $email");
        
        // Agregar nota al perfil del usuario
        update_user_meta($user_id, 'ldap_synced', 'yes');
        update_user_meta($user_id, 'ldap_temp_password', $temp_password);
        
    } else {
        error_log("WP-LDAP: Error al crear usuario $username en LDAP: " . ldap_error($ldap_conn));
    }
    
    ldap_close($ldap_conn);
}

function generate_ssha_password($password) {
    // Generar salt aleatorio
    $salt = '';
    for ($i = 0; $i < 4; $i++) {
        $salt .= chr(rand(0, 255));
    }
    
    // Crear hash SSHA
    $hash = base64_encode(sha1($password . $salt, true) . $salt);
    
    return '{SSHA}' . $hash;
}

// Agregar aviso en el perfil del usuario
add_action('show_user_profile', 'show_ldap_sync_notice');
add_action('edit_user_profile', 'show_ldap_sync_notice');

function show_ldap_sync_notice($user) {
    $synced = get_user_meta($user->ID, 'ldap_synced', true);
    $temp_password = get_user_meta($user->ID, 'ldap_temp_password', true);
    
    if ($synced === 'yes') {
        ?>
        <h3>Acceso a Webmail</h3>
        <table class="form-table">
            <tr>
                <th>Estado LDAP</th>
                <td>
                    <span style="color: green;">✓ Sincronizado con LDAP</span>
                    <p class="description">
                        Puedes acceder a webmail con:<br>
                        <strong>Usuario:</strong> <?php echo esc_html($user->user_email); ?><br>
                        <strong>Contraseña:</strong> <?php echo esc_html($temp_password); ?><br>
                        <em>URL: <a href="https://webmail.patitohosting.licic" target="_blank">https://webmail.patitohosting.licic</a></em>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
}

// Agregar columna en listado de usuarios
add_filter('manage_users_columns', 'add_ldap_column');
function add_ldap_column($columns) {
    $columns['ldap_sync'] = 'LDAP';
    return $columns;
}

add_action('manage_users_custom_column', 'show_ldap_column_content', 10, 3);
function show_ldap_column_content($value, $column_name, $user_id) {
    if ($column_name == 'ldap_sync') {
        $synced = get_user_meta($user_id, 'ldap_synced', true);
        return $synced === 'yes' ? '✓' : '—';
    }
    return $value;
}
