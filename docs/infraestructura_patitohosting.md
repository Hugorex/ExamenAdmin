# Arquitectura de Infraestructura - PatitoHosting.licic

## Diagrama de Red

```
192.168.56.0/24 (VirtualBox Host-Only)
│
├── 192.168.56.10 - www.patitohosting.licic (Web Server)
│   ├── Apache 2.4.65
│   ├── PHP 8.2
│   └── WordPress
│
├── 192.168.56.11 - db.patitohosting.licic (Database Server)
│   └── MariaDB 10.11.14
│
├── 192.168.56.12 - ns.patitohosting.licic (DNS Server)
│   └── BIND9 9.18
│
├── 192.168.56.13 - email.patitohosting.licic (Mail Server)
│   ├── Postfix 3.7
│   ├── Dovecot 2.3
│   ├── Amavis
│   ├── SpamAssassin
│   └── ClamAV
│
└── 192.168.56.14 - ldap.patitohosting.licic (LDAP Server)
    └── OpenLDAP 2.5
```

## Especificaciones por Servidor

### Web Server (www)
- **Hostname**: www.patitohosting.licic
- **IP**: 192.168.56.10
- **RAM**: 2 GB
- **CPU**: 2 cores
- **Servicios**: Apache, PHP, WordPress
- **Puertos**: 80 (HTTP), 443 (HTTPS)

### Database Server (db)
- **Hostname**: db.patitohosting.licic
- **IP**: 192.168.56.11
- **RAM**: 1 GB
- **CPU**: 1 core
- **Servicios**: MariaDB
- **Puertos**: 3306 (MySQL)

### DNS Server (ns)
- **Hostname**: ns.patitohosting.licic
- **IP**: 192.168.56.12
- **RAM**: 512 MB
- **CPU**: 1 core
- **Servicios**: BIND9
- **Puertos**: 53 (DNS)

### Mail Server (email)
- **Hostname**: email.patitohosting.licic
- **IP**: 192.168.56.13
- **RAM**: 2 GB
- **CPU**: 2 cores
- **Servicios**: Postfix, Dovecot, Amavis, ClamAV
- **Puertos**: 25, 587 (SMTP), 143, 993 (IMAP), 110, 995 (POP3)

### LDAP Server (ldap)
- **Hostname**: ldap.patitohosting.licic
- **IP**: 192.168.56.14
- **RAM**: 1 GB
- **CPU**: 1 core
- **Servicios**: OpenLDAP
- **Puertos**: 389 (LDAP)

## Flujos de Comunicación

1. **Cliente → www**: Acceso web vía HTTP/HTTPS
2. **www → db**: Consultas WordPress a MySQL
3. **www → ldap**: Autenticación admin
4. **email → ldap**: Validación usuarios correo
5. **email → Internet**: SMTP entrada/salida
6. **Todos → ns**: Resolución DNS

## Seguridad

- **Firewall**: UFW deshabilitado (entorno de desarrollo)
- **SSL/TLS**: Certificados autofirmados
- **Autenticación**: LDAP centralizada
- **Encriptación**: TLS en SMTP, IMAP
