<?php
/**
 * Plugin Name: WP LDAP User Sync
 * Description: Sincroniza usuarios de WordPress con LDAP para acceso a webmail
 * Version: 1.0
 * Author: Auto-generated
 */

// Variable global para almacenar la contraseña temporalmente
global $wp_ldap_user_password;
$wp_ldap_user_password = null;

// Capturar la contraseña antes de que WordPress la hashee
add_action('user_register', 'capture_user_password', 1, 1);
function capture_user_password($user_id) {
    global $wp_ldap_user_password;
    
    // Intentar obtener la contraseña de varias fuentes
    if (isset($_POST['pass1']) && !empty($_POST['pass1'])) {
        $wp_ldap_user_password = $_POST['pass1'];
    } elseif (isset($_POST['user_pass']) && !empty($_POST['user_pass'])) {
        $wp_ldap_user_password = $_POST['user_pass'];
    } elseif (isset($_POST['password']) && !empty($_POST['password'])) {
        $wp_ldap_user_password = $_POST['password'];
    }
}

// Hook cuando se crea un nuevo usuario en WordPress
add_action('user_register', 'sync_user_to_ldap', 10, 1);

function sync_user_to_ldap($user_id) {
    global $wp_ldap_user_password;
    
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
    if (!ldap_bind($ldap_conn, $ldap_admin_dn, $ldap_admin_pass)) {
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
    $display_name = (!empty($user->display_name)) ? $user->display_name : $username;
    
    // Usar la contraseña capturada o una temporal
    $password = (!empty($wp_ldap_user_password)) ? $wp_ldap_user_password : 'password123';
    $password_hash = generate_ssha_password($password);
    
    // Limpiar la variable global
    $wp_ldap_user_password = null;
    
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
        error_log("WP-LDAP: Usuario $username sincronizado exitosamente con email $email (password: $password)");
        
        // Agregar nota al perfil del usuario
        update_user_meta($user_id, 'ldap_synced', 'yes');
        update_user_meta($user_id, 'ldap_password', $password);
        
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
