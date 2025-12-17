# Infraestructura PatitoHosting - Laboratorio Vagrant

## 📋 Descripción del Proyecto

Infraestructura completa de servicios implementada con Vagrant y Ansible que incluye:
- **DNS** (BIND9)
- **LDAP** (OpenLDAP) 
- **Base de Datos** (MariaDB) con seguridad reforzada
- **Servidor Web** (Apache + WordPress)
- **Correo Electrónico** (Postfix + Dovecot + Roundcube)
- **Firewall** (UFW) en todos los servidores

## 🖥️ Arquitectura

| VM | Hostname | IP | Servicios |
|----|----------|-----|-----------|
| ns | ns.patitohosting.licic | 192.168.56.12 | BIND9 (DNS) |
| ldap | ldap.patitohosting.licic | 192.168.56.14 | OpenLDAP |
| db | db.patitohosting.licic | 192.168.56.11 | MariaDB |
| www | www.patitohosting.licic | 192.168.56.10 | Apache, WordPress |
| email | email.patitohosting.licic | 192.168.56.13 | Postfix, Dovecot, Roundcube |

## 🔧 Requisitos Previos

- **Vagrant** instalado (versión 2.0+)
- **VirtualBox** instalado
- **Git** instalado
- Al menos **8GB de RAM** disponible
- Al menos **20GB** de espacio en disco

## 🚀 Instalación y Despliegue

### 1. Clonar el Repositorio

```bash
git clone https://github.com/Hugorex/ExamenAdmin.git
cd ExamenAdmin
```

### 2. Levantar las Máquinas Virtuales

```bash
# Levantar todas las VMs (primera vez puede tardar 10-15 minutos)
vagrant up

# Verificar estado de las VMs
vagrant status
```

### 3. Ejecutar Aprovisionamiento con Ansible

```bash
# Primera ejecución del playbook
vagrant ssh email -c "cd /vagrant/ansible && ansible-playbook -i inventory/hosts site.yml"

# Segunda ejecución para demostrar IDEMPOTENCIA
vagrant ssh email -c "cd /vagrant/ansible && ansible-playbook -i inventory/hosts site.yml"
```

**Nota:** La segunda ejecución debe mostrar principalmente tareas "ok" (en verde) y pocas o ninguna "changed" (en amarillo), demostrando que Ansible es idempotente.

### Alternativa (desde host local):

```bash
ansible-playbook ansible/site.yml
```

## ✅ Verificación de la Infraestructura

### PASO 1: Verificar Conectividad entre VMs

```bash
# Verificar estado de todas las VMs
vagrant status

# Probar conectividad desde ns hacia otros servidores
vagrant ssh ns -c "ping -c 3 192.168.56.10"
vagrant ssh ns -c "ping -c 3 192.168.56.11"
vagrant ssh www -c "ping -c 3 192.168.56.12"
vagrant ssh www -c "ping -c 3 192.168.56.13"
vagrant ssh db -c "ping -c 3 192.168.56.14"
```

### PASO 2: Verificar Firewall (UFW)

```bash
# Verificar estado de UFW en todos los servidores
vagrant ssh ns -c "sudo ufw status numbered"
vagrant ssh ldap -c "sudo ufw status numbered"
vagrant ssh db -c "sudo ufw status numbered"
vagrant ssh www -c "sudo ufw status numbered"
vagrant ssh email -c "sudo ufw status numbered"
```

**Resultado Esperado:**
- Status: active
- Puerto 22 (SSH): ALLOW desde cualquier origen
- Puerto 3306 (MySQL): ALLOW solo desde 192.168.56.0/24 (en servidor db)
- Otros puertos según el servicio de cada VM

### PASO 3: Verificar Base de Datos

#### 3.1 Conectar a MariaDB como root

```bash
# Conectar a la base de datos
vagrant ssh db -c "mysql -u root -prootpass123"
```

**Comandos dentro de MySQL:**
```sql
SHOW DATABASES;
USE wordpress_db;
SHOW TABLES;
EXIT;
```

#### 3.2 Ver Bases de Datos y Usuarios

```bash
# Listar bases de datos
vagrant ssh db -c "mysql -u root -prootpass123 -e 'SHOW DATABASES;'"

# Listar usuarios de MySQL
vagrant ssh db -c "mysql -u root -prootpass123 -e \"SELECT user, host FROM mysql.user ORDER BY user, host;\""
```

**Resultado Esperado:**
- Bases de datos: `wordpress_db`, `roundcube`
- Usuarios: `root@localhost`, `wp_user@192.168.56.10`, `roundcube@192.168.56.10`
- **NO debe existir** `admin@192.168.56.%` (eliminado por seguridad)

#### 3.3 Verificar WordPress Conectado a BD Remota

```bash
# Ver configuración de WordPress
vagrant ssh www -c "grep -E 'DB_HOST|DB_NAME|DB_USER' /var/www/html/wordpress/wp-config.php"
```

**Resultado Esperado:**
```
define( 'DB_NAME', 'wordpress_db' );
define( 'DB_USER', 'wp_user' );
define( 'DB_HOST', '192.168.56.11' );
```

#### 3.4 Verificar Privilegios Mínimos de wp_user

```bash
# Ver grants de wp_user
vagrant ssh db -c "mysql -u root -prootpass123 -e \"SHOW GRANTS FOR 'wp_user'@'192.168.56.10';\""
```

**Resultado Esperado:**
```
GRANT USAGE ON *.* TO `wp_user`@`192.168.56.10`
GRANT SELECT, INSERT, UPDATE, DELETE ON `wordpress_db`.* TO `wp_user`@`192.168.56.10`
```

**Seguridad Aplicada:**
- ✅ Host restringido a `192.168.56.10` (no `%`)
- ✅ Sin privilegios globales (solo `USAGE` en `*.*`)
- ✅ Solo SELECT, INSERT, UPDATE, DELETE en `wordpress_db.*`
- ✅ Sin privilegios DROP, CREATE USER, GRANT OPTION

#### 3.5 Probar Conexión Remota con wp_user

```bash
# Conectar desde www a db usando wp_user
vagrant ssh www -c "mysql -h 192.168.56.11 -u wp_user -pwppass123 --skip-ssl wordpress_db"
```

**Comandos dentro de MySQL:**
```sql
-- Verificar servidor
SELECT @@hostname AS servidor_bd, NOW() AS hora;

-- Contar usuarios de WordPress
SELECT COUNT(*) AS total_users FROM wp_users;

-- Ver usuarios de WordPress
SELECT ID, user_login, user_email FROM wp_users LIMIT 10;

EXIT;
```

#### 3.6 Ver Usuarios de WordPress

```bash
# Listar usuarios de WordPress desde la BD
vagrant ssh db -c "mysql -u root -prootpass123 wordpress_db -e \"SELECT ID, user_login, user_email FROM wp_users LIMIT 10;\""
```

### PASO 4: Verificar Servicio de Correo

#### 4.1 Ver Buzones de Correo

```bash
# Listar todos los buzones
vagrant ssh email -c "sudo ls -la /var/vmail/"

# Ver contenido del buzón de maria
vagrant ssh email -c "sudo ls -la /var/vmail/maria@patitohosting.licic/"
```

**Estructura Maildir:**
- `new/` - Correos nuevos no leídos
- `cur/` - Correos leídos
- `tmp/` - Archivos temporales

#### 4.2 Acceder a Roundcube (Webmail)

Abrir navegador en:
```
https://webmail.patitohosting.licic
```

**Usuarios de prueba:**
- maria / maria123
- jc / jc123
- pedro / pedro123

### PASO 5: Acceder a WordPress

Abrir navegador en:
```
http://www.patitohosting.licic/wordpress
```

**Credenciales de administrador:** (configuradas durante instalación)

## 🔒 Características de Seguridad Implementadas

### Base de Datos
1. **Usuario root:** Solo acceso local (`root@localhost`)
2. **Usuario admin remoto:** ELIMINADO (anteriormente `admin@192.168.56.%`)
3. **Usuario wp_user:** 
   - Host restringido a `192.168.56.10`
   - Privilegios mínimos (SELECT, INSERT, UPDATE, DELETE)
   - Sin privilegios DROP ni administrativos
4. **Usuario roundcube:** Aislado a su propia base de datos

### Firewall (UFW)
- **Puerto 22 (SSH):** Abierto (necesario para Vagrant)
- **Puerto 3306 (MySQL):** Restringido a red local `192.168.56.0/24`
- **Política por defecto:** DENY incoming, ALLOW outgoing

### Red
- **Bind address MySQL:** `0.0.0.0` pero protegido por firewall
- **Usuarios BD:** Restringidos por IP origen
- **Sin usuarios anónimos** en MySQL

## 🛠️ Administración

### Conectarse a las VMs

```bash
vagrant ssh ns      # DNS
vagrant ssh ldap    # LDAP
vagrant ssh db      # Base de datos
vagrant ssh www     # Servidor web
vagrant ssh email   # Servidor correo
```

### Administrar Base de Datos

```bash
# Desde dentro de la VM db
vagrant ssh db
mysql -u root -prootpass123

# Desde host local (un solo comando)
vagrant ssh db -c "mysql -u root -prootpass123"
```

**IMPORTANTE:** El usuario `admin` remoto fue eliminado por seguridad. Para administrar la BD usar:
- SSH a la VM: `vagrant ssh db -c "mysql -u root -p"`
- O desde dentro de la VM después de conectarse por SSH

### Apagar y Reiniciar

```bash
# Apagar todas las VMs (seguro, configuración persistente)
vagrant halt

# Reiniciar todas las VMs
vagrant up

# Destruir y recrear (CUIDADO: borra todo)
vagrant destroy -f
vagrant up
```

## 📁 Estructura del Proyecto

```
ExamenAdmin/
├── Vagrantfile                 # Definición de las 5 VMs
├── ansible/
│   ├── site.yml               # Playbook principal
│   ├── inventory/
│   │   └── hosts              # Inventario de servidores
│   └── roles/
│       ├── common/            # Configuración común (UFW, SSH)
│       ├── dns/               # BIND9
│       ├── ldap/              # OpenLDAP
│       ├── database/          # MariaDB con seguridad reforzada
│       ├── webserver/         # Apache + WordPress
│       └── mail/              # Postfix + Dovecot + Roundcube
└── README.md                  # Este archivo
```

## 🔍 Archivos de Configuración Clave

### Usuarios de Base de Datos
**Ubicación:** `ansible/roles/database/tasks/main.yml`
- Líneas 55-67: Creación de `wp_user` con privilegios mínimos
- Líneas 90-99: Usuario `admin` comentado (eliminado por seguridad)

### Reglas de Firewall
**Ubicación:** `ansible/roles/common/tasks/main.yml`
- Líneas 59-84: Configuración de UFW (políticas, reglas SSH)

**Ubicación:** `ansible/roles/database/tasks/main.yml`
- Reglas específicas para puerto 3306 (MySQL)

### Buzones de Correo
**Ubicación:** `ansible/roles/mail/vars/main.yml`
- `virtual_mailbox_base: /var/vmail`

## ⚠️ Notas Importantes

1. **Contraseñas por defecto:** Este es un entorno de desarrollo/aprendizaje. Las contraseñas están en texto plano en los archivos de configuración (rootpass123, wppass123, etc.). **NO usar en producción.**

2. **Firewall y SSH:** Si tienes problemas de conectividad SSH, verifica que UFW permita el puerto 22. Las reglas están configuradas para permitir SSH desde cualquier origen (necesario para Vagrant).

3. **Primera ejecución:** La primera vez que ejecutes `vagrant up` tardará varios minutos mientras descarga la imagen base de Debian 12 y aprovisiona las 5 VMs.

4. **Requisitos de RAM:** Se recomienda al menos 8GB de RAM. Cada VM usa aproximadamente 1-1.5GB.

5. **Persistencia:** La configuración de base de datos, usuarios, y reglas de firewall persiste entre reinicios (`vagrant halt` / `vagrant up`). Solo se pierde si haces `vagrant destroy`.

## 🐛 Resolución de Problemas

### Error de SSH
```bash
# Verificar que UFW permite SSH
vagrant ssh <vm> -c "sudo ufw status | grep 22"
```

### Error de Conectividad MySQL
```bash
# Verificar que MySQL escucha en todas las interfaces
vagrant ssh db -c "sudo ss -tlnp | grep 3306"

# Verificar firewall
vagrant ssh db -c "sudo ufw status | grep 3306"
```

### WordPress no carga
```bash
# Verificar servicio Apache
vagrant ssh www -c "sudo systemctl status apache2"

# Verificar configuración de WordPress
vagrant ssh www -c "cat /var/www/html/wordpress/wp-config.php | grep DB_"
```

### Roundcube no carga
```bash
# Verificar servicios de correo
vagrant ssh email -c "sudo systemctl status postfix"
vagrant ssh email -c "sudo systemctl status dovecot"
```

## 📚 Comandos Útiles de Resumen

```bash
# Verificar todo en un script
vagrant status
vagrant ssh db -c "mysql -u root -prootpass123 -e 'SHOW DATABASES;'"
vagrant ssh www -c "grep DB_HOST /var/www/html/wordpress/wp-config.php"
vagrant ssh email -c "sudo ls -la /var/vmail/"
vagrant ssh db -c "sudo ufw status numbered"
```

## 👨‍🏫 Para el Profesor

Este proyecto implementa una infraestructura completa de servicios con:
- ✅ Automatización completa con Ansible
- ✅ Idempotencia verificada
- ✅ Seguridad reforzada en base de datos
- ✅ Firewall configurado en todos los servidores
- ✅ Integración WordPress-LDAP
- ✅ Servicio de correo funcional con Roundcube

**Pasos mínimos para validar:**
1. `vagrant up`
2. Ejecutar playbook Ansible (2 veces para ver idempotencia)
3. Ejecutar comandos de verificación de las secciones anteriores

---

**Autor:** Hugo Rex  
**Repositorio:** https://github.com/Hugorex/ExamenAdmin  
**Fecha:** Diciembre 2025
