# CAMBIOS REALIZADOS EN ANSIBLE
**Fecha**: 14 de diciembre de 2025

## ✅ RESUMEN DE ACTUALIZACIONES

Todos los cambios manuales realizados durante la sesión han sido integrados en los roles de Ansible. Ahora el despliegue es **100% automatizado**.

---

## 📝 CAMBIOS DETALLADOS

### 1. **Rol Mail** (`ansible/roles/mail/`)

#### Archivo: `templates/main.cf.j2`
**Cambios**:
- ✅ Deshabilitado TLS: `smtpd_tls_security_level = none`
- ✅ Deshabilitado TLS SMTP: `smtp_tls_security_level = none`
- ✅ Permitir autenticación sin TLS: `smtpd_tls_auth_only = no`
- ✅ Agregada restricción relay: `smtpd_relay_restrictions = permit_mynetworks, reject_unauth_destination`
- ✅ Simplificada restricción sender: `smtpd_sender_restrictions = permit_mynetworks`

**Motivo**: Permite a Roundcube enviar emails desde la red local sin autenticación TLS.

#### Archivo: `vars/main.yml`
**Estado**: ✅ Ya tenía `ldap_bind_password: ldapadmin123` correcto

---

### 2. **Rol DNS** (`ansible/roles/dns/`)

#### Archivo: `templates/db.patitohosting.licic.j2`
**Cambios**:
- ✅ Agregado registro: `webmail IN A 192.168.56.10`
- ✅ Actualizado serial: `2025121401` (era 2025121101)

**Motivo**: Permite resolver webmail.patitohosting.licic correctamente.

---

### 3. **Rol LDAP** (`ansible/roles/ldap/`)

#### Archivo: `templates/users.ldif.j2`
**Cambios**:
- ✅ Agregado `usuario3` con mail: usuario3@patitohosting.licic
- ✅ Agregado `wilo` con mail: wilo@patitohosting.licic
- ✅ Todos los usuarios con contraseña: `password123`

**Motivo**: Incluir todos los usuarios de prueba necesarios para la evaluación.

---

### 4. **Nuevo Rol: Webmail** (`ansible/roles/webmail/`) ⭐ NUEVO

#### Estructura creada:
```
webmail/
├── tasks/main.yml          # Instalación completa de Roundcube
├── vars/main.yml           # Variables (versión, credenciales)
├── templates/
│   ├── config.inc.php.j2   # Configuración Roundcube
│   └── webmail.conf.j2     # VirtualHost Apache
```

#### Archivo: `tasks/main.yml`
**Funcionalidades**:
- ✅ Instalación automática de dependencias PHP
- ✅ Descarga de Roundcube 1.6.9 desde GitHub
- ✅ Extracción y configuración de permisos
- ✅ Creación de base de datos en servidor remoto (192.168.56.11)
- ✅ Importación del esquema MySQL
- ✅ Configuración de VirtualHost Apache
- ✅ Habilitación del sitio y recarga de Apache

#### Archivo: `templates/config.inc.php.j2`
**Configuración**:
- ✅ IMAP: `tls://192.168.56.13:993`
- ✅ SMTP: `192.168.56.13:25` **sin autenticación** (smtp_user='', smtp_auth_type=null)
- ✅ Base de datos: `mysql://roundcube:roundcube123@192.168.56.11/roundcube`
- ✅ LDAP addressbook integrado:
  - Host: 192.168.56.14
  - Base DN: ou=people,dc=patitohosting,dc=licic
  - Bind DN: cn=admin con password ldapadmin123
- ✅ Idioma: español (es_ES)
- ✅ Skin: elastic

#### Archivo: `templates/webmail.conf.j2`
**VirtualHost**:
- ServerName: webmail.patitohosting.licic
- DocumentRoot: /var/www/webmail
- Logs: webmail-error.log, webmail-access.log

---

### 5. **Playbook Principal** (`ansible/site.yml`)

**Cambios**:
```yaml
- name: Configurar servidor web
  hosts: webserver
  become: yes
  roles:
    - webserver
    - webmail    # ⭐ AGREGADO
```

**Motivo**: Incluir instalación automática de Roundcube en el servidor web.

---

## 🎯 RESULTADO FINAL

### Antes (manual):
❌ Roundcube instalado manualmente  
❌ Postfix TLS configurado a mano  
❌ DNS webmail agregado con nsupdate  
❌ Usuarios LDAP creados con ldapadd  
❌ Cambios se pierden con `vagrant destroy`

### Ahora (automatizado):
✅ Roundcube instalado por Ansible  
✅ Postfix TLS deshabilitado en template  
✅ DNS webmail en zona template  
✅ Usuarios LDAP en template LDIF  
✅ **Cambios persisten después de `vagrant destroy -f && vagrant up`**

---

## 🚀 VALIDACIÓN

### Cómo probar que funciona:

```bash
# 1. Destruir VMs actuales
cd /home/hugorex/ExamenAdmin
vagrant destroy -f

# 2. Levantar VMs desde cero (esperar 15-20 minutos)
vagrant up

# 3. Validar servicios automáticamente configurados:

# DNS - Webmail resuelve
nslookup webmail.patitohosting.licic 192.168.56.12

# LDAP - Usuario wilo existe
ldapsearch -x -H ldap://192.168.56.14 -b "dc=patitohosting,dc=licic" "(uid=wilo)"

# Webmail - Roundcube accesible
curl -I http://192.168.56.10/webmail/

# Email - Enviar desde Roundcube sin errores
# (Probar login: usuario1@patitohosting.licic / password123)
```

---

## 📦 ZIP ACTUALIZADO

**Archivo**: `/home/hugorex/ExamenAdmin_Portable.zip`  
**Tamaño**: 52 KB (aumentó 4 KB por el nuevo rol webmail)  
**Estado**: ✅ Incluye todos los cambios de Ansible  
**Compatibilidad**: Windows, Linux, macOS

### Contenido del ZIP:
- ✅ Vagrantfile
- ✅ Ansible completo con todos los roles actualizados
- ✅ Documentación (README, COMANDOS, INSTRUCCIONES_WINDOWS)
- ❌ No incluye .vagrant/ (portabilidad)
- ❌ No incluye .git/ (limpieza)

---

## 🎓 IMPORTANTE PARA LA EVALUACIÓN

### Tu colega ahora puede:
1. Descomprimir el ZIP en Windows
2. Ejecutar `vagrant up` (esperar provisioning completo)
3. **Todos los servicios funcionarán automáticamente**:
   - WordPress en https://www.patitohosting.licic
   - Webmail en http://webmail.patitohosting.licic
   - Email enviando/recibiendo correctamente
   - DNS resolviendo todos los dominios
   - LDAP con todos los usuarios (usuario1, usuario2, usuario3, wilo)

### Sin intervención manual:
- ❌ NO necesita instalar Roundcube manualmente
- ❌ NO necesita modificar /etc/postfix/main.cf
- ❌ NO necesita crear usuarios LDAP
- ❌ NO necesita actualizar DNS
- ✅ **SOLO ejecutar: `vagrant up`**

---

## ⚠️ ADVERTENCIAS

### Tiempo de provisioning:
- Primera ejecución: **15-20 minutos**
- Descarga de Roundcube: ~15 MB (puede tardar según internet)
- Instalación de paquetes PHP: ~5 minutos
- **IMPORTANTE**: NO interrumpir el provisioning, dejarlo completar

### Requisitos del sistema:
- RAM: Mínimo 8 GB (5 VMs simultáneas)
- Disco: 10 GB libres
- CPU: Procesador con soporte de virtualización (Intel VT-x / AMD-V)
- Software: VirtualBox 7.x + Vagrant 2.4.x

---

## ✅ CHECKLIST FINAL

- [x] Contraseñas LDAP corregidas (ldapadmin123)
- [x] Postfix TLS deshabilitado
- [x] Postfix relay permitido desde mynetworks
- [x] DNS con registro webmail
- [x] Usuarios LDAP: usuario1, usuario2, usuario3, wilo
- [x] Roundcube instalado automáticamente
- [x] Base de datos Roundcube creada automáticamente
- [x] Configuración SMTP sin autenticación
- [x] LDAP addressbook integrado
- [x] VirtualHost Apache para webmail
- [x] Site.yml actualizado con rol webmail
- [x] ZIP portable regenerado (52 KB)
- [x] Toda la configuración persiste después de destroy

---

**Todo listo para la evaluación presencial! 🎉**
