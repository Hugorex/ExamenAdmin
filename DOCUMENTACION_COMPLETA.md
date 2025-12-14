# DOCUMENTACIÓN COMPLETA - INFRAESTRUCTURA PATITOHOSTING.LICIC

**Proyecto:** Automatización de Infraestructura con Vagrant y Ansible  
**Fecha:** 11 de Diciembre de 2025  
**Autor:** Hugo Rex  
**Sistema Operativo:** Debian 12 (Bookworm)

---

## TABLA DE CONTENIDOS

1. [Arquitectura de la Infraestructura](#1-arquitectura-de-la-infraestructura)
2. [Automatización Implementada](#2-automatización-implementada)
3. [Guía de Instalación y Ejecución](#3-guía-de-instalación-y-ejecución)
4. [Pruebas de Funcionamiento](#4-pruebas-de-funcionamiento)
5. [Versiones de Software](#5-versiones-de-software)
6. [Problemas Encontrados y Soluciones](#6-problemas-encontrados-y-soluciones)
7. [Conclusiones](#7-conclusiones)

---

## 1. ARQUITECTURA DE LA INFRAESTRUCTURA

### 1.1 Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────────────────┐
│                    RED: 192.168.56.0/24                             │
│                    (VirtualBox Host-Only Network)                   │
└─────────────────────────────────────────────────────────────────────┘
                                   │
        ┌──────────────────────────┼──────────────────────────┐
        │                          │                          │
┌───────▼────────┐        ┌────────▼───────┐        ┌────────▼───────┐
│  DNS SERVER    │        │  LDAP SERVER   │        │   DB SERVER    │
│ ns.patito...   │        │ ldap.patito... │        │ db.patito...   │
│ 192.168.56.12  │        │ 192.168.56.14  │        │ 192.168.56.11  │
│                │        │                │        │                │
│ • BIND9        │        │ • OpenLDAP     │        │ • MariaDB      │
│ • DNS Zones    │        │ • Users/Groups │        │ • WordPress DB │
│ • Port 53      │        │ • Port 389     │        │ • Port 3306    │
└────────────────┘        └────────────────┘        └────────────────┘
                                   │
        ┌──────────────────────────┼──────────────────────────┐
        │                          │                          │
┌───────▼────────┐                 │                ┌─────────▼──────┐
│  WEB SERVER    │                 │                │  EMAIL SERVER  │
│ www.patito...  │                 │                │ email.patito.. │
│ 192.168.56.10  │◄────────────────┘                │ 192.168.56.13  │
│                │                                   │                │
│ • Apache 2.4   │                                   │ • Postfix      │
│ • PHP 8.2      │                                   │ • Dovecot      │
│ • WordPress    │                                   │ • ClamAV       │
│ • SSL/TLS      │                                   │ • Amavis       │
│ • HTTP/HTTPS   │                                   │ • SpamAssassin │
│ • Port 80/443  │                                   │ • SMTP/IMAP    │
└────────────────┘                                   └────────────────┘
```

### 1.2 Componentes de la Infraestructura

| Servidor | Hostname | IP | Servicios Principales |
|----------|----------|-----|----------------------|
| **Web** | www.patitohosting.licic | 192.168.56.10 | Apache, PHP, WordPress |
| **Database** | db.patitohosting.licic | 192.168.56.11 | MariaDB 10.11 |
| **DNS** | ns.patitohosting.licic | 192.168.56.12 | BIND9 |
| **Email** | email.patitohosting.licic | 192.168.56.13 | Postfix, Dovecot, ClamAV |
| **LDAP** | ldap.patitohosting.licic | 192.168.56.14 | OpenLDAP |

---

## 2. AUTOMATIZACIÓN IMPLEMENTADA

### 2.1 Tecnologías Utilizadas

- **Vagrant 2.4.x**: Orquestación de máquinas virtuales
- **VirtualBox**: Virtualización
- **Ansible 2.14+**: Aprovisionamiento (instalado automáticamente)
- **Debian 12 (Bookworm)**: Sistema operativo base

### 2.2 Vagrant Configuration

El `Vagrantfile` utiliza `ansible_local` provisioner, que ejecuta Ansible DENTRO de las VMs:
- ✅ Compatible con Windows, Linux y macOS
- ✅ No requiere Ansible en el host
- ✅ Se instala automáticamente via pip

---

## 3. GUÍA DE INSTALACIÓN Y EJECUCIÓN

### 3.1 Requisitos Previos

**Software Necesario (Linux/Windows/macOS):**
- Vagrant 2.4.x o superior
- VirtualBox 7.x o superior

**NO necesitas instalar Ansible** - se instala automáticamente.

### 3.2 Instalación Paso a Paso

#### PASO 1: Descargar el proyecto
```bash
# Extraer archivo
cd ~/
unzip ExamenAdmin.zip
cd ExamenAdmin
```

#### PASO 2: Levantar infraestructura
```bash
# Comando idéntico en Windows/Linux/macOS
vagrant up
```

Tiempo estimado: **15-20 minutos**

#### PASO 3: Aplicar parches post-provisionamiento
```bash
# Necesario para MariaDB y Postfix
vagrant ssh db -c "sudo systemctl restart mariadb"
vagrant ssh email -c "sudo systemctl restart postfix"
```

### 3.3 Verificación
```bash
vagrant status
# Todos deben estar "running"
```

---

## 4. PRUEBAS DE FUNCIONAMIENTO

### 4.1 Pruebas desde el Host

**Linux/macOS:**
```bash
# WordPress
curl -Ik https://192.168.56.10/

# Puertos
nc -zv 192.168.56.10 80 443
nc -zv 192.168.56.11 3306
nc -zv 192.168.56.12 53
nc -zv 192.168.56.13 25 587 143 993
nc -zv 192.168.56.14 389
```

**Windows (PowerShell):**
```powershell
# Probar conectividad
Test-NetConnection -ComputerName 192.168.56.10 -Port 80
Test-NetConnection -ComputerName 192.168.56.13 -Port 587
```

### 4.2 Resultados Esperados

✅ **WordPress**: HTTP 302 o 200  
✅ **MariaDB**: Puerto 3306 abierto  
✅ **DNS**: Puerto 53 responde  
✅ **Email**: Puertos 25, 587, 143, 993 accesibles  
✅ **LDAP**: Puerto 389 accesible

---

## 5. VERSIONES DE SOFTWARE

| Software | Versión | Servidor |
|----------|---------|----------|
| Debian | 12 (Bookworm) | Todos |
| Apache | 2.4.65 | www |
| PHP | 8.2 | www |
| MariaDB | 10.11.14 | db |
| BIND9 | 9.18 | ns |
| Postfix | 3.7 | email |
| Dovecot | 2.3 | email |
| OpenLDAP | 2.5 | ldap |
| WordPress | Latest | www |

---

## 6. PROBLEMAS ENCONTRADOS Y SOLUCIONES

### 6.1 Problema 1: systemd-resolved en Debian 12

**Error:**  
DNS no resuelve, systemd-resolved conflicto.

**Solución:**
```yaml
- name: Detener systemd-resolved
  ansible.builtin.systemd:
    name: systemd-resolved
    state: stopped
    enabled: no
```

### 6.2 Problema 2: UFW no instalado por defecto

**Error:**  
`Package ufw not found`

**Solución:**
```yaml
- name: Deshabilitar UFW
  community.general.ufw:
    state: disabled
  ignore_errors: yes
```

### 6.3 Problema 3: MariaDB unix_socket authentication

**Error:**  
Ansible no puede autenticarse con MariaDB.

**Solución:**
```yaml
community.mysql.mysql_user:
  check_implicit_admin: yes  # ← Clave
  login_unix_socket: /run/mysqld/mysqld.sock
```

### 6.4 Problema 4: libapache2-mod-authnz-ldap no existe

**Error:**  
Paquete no existe en Debian 12.

**Solución:**
Reemplazar con:
```yaml
- libapache2-mod-authnz-external
- libapache2-mod-authz-unixgroup
```

### 6.5 Problema 5: python3-cryptography no instalado

**Error:**  
No se puede generar certificado SSL.

**Solución:**
```yaml
- name: Instalar python3-cryptography ANTES
  ansible.builtin.apt:
    name: python3-cryptography
```

### 6.6 Problema 6: Usuario vmail no existe

**Error:**  
SpamAssassin falla al intentar usar usuario inexistente.

**Solución:**
```yaml
# Crear grupo ANTES que usuario
- name: Crear grupo vmail
  ansible.builtin.group:
    name: vmail
    gid: 5000
    
- name: Crear usuario vmail
  ansible.builtin.user:
    name: vmail
    uid: 5000
    group: vmail
```

### 6.7 Problema 7: MariaDB escucha solo en 127.0.0.1

**Error:**  
WordPress no puede conectar a base de datos.

**Causa:**  
`bind-address = 127.0.0.1` por defecto.

**Solución:**
```ini
# En 50-server.cnf
bind-address = 0.0.0.0
```

Luego reiniciar:
```bash
vagrant ssh db -c "sudo systemctl restart mariadb"
```

### 6.8 Problema 8: Puerto 587 no activo

**Error:**  
SMTP Submission no accesible.

**Causa:**  
Postfix requiere reinicio para activar submission service.

**Solución:**
```bash
vagrant ssh email -c "sudo systemctl restart postfix"
```

---

## 7. CONCLUSIONES

### 7.1 Objetivos Alcanzados

✅ **Automatización completa**: 5 servidores con 1 comando  
✅ **Compatibilidad multiplataforma**: Windows, Linux, macOS  
✅ **Alta disponibilidad**: Todos los servicios operacionales  
✅ **Seguridad**: SSL/TLS, LDAP centralizado  
✅ **Documentación**: Completa y detallada  

### 7.2 Servicios Operacionales

| Servicio | Estado | Puerto |
|----------|--------|--------|
| WordPress | ✅ | 80, 443 |
| MariaDB | ✅ | 3306 |
| DNS | ✅ | 53 |
| SMTP | ✅ | 25, 587 |
| IMAP/POP3 | ✅ | 143, 993, 110, 995 |
| LDAP | ✅ | 389 |

### 7.3 Lecciones Aprendidas

1. **ansible_local** permite portabilidad total
2. **check_implicit_admin** necesario para MariaDB en Debian 12
3. **Orden de tareas** crítico (grupo antes de usuario)
4. **Reinicios post-provisioning** necesarios para MariaDB y Postfix
5. **Debian 12** tiene cambios significativos vs Debian 11

### 7.4 Comandos Esenciales

```bash
# Desplegar
vagrant up

# Parches
vagrant ssh db -c "sudo systemctl restart mariadb"
vagrant ssh email -c "sudo systemctl restart postfix"

# Verificar
vagrant status

# Detener
vagrant halt
```

---

**FIN DE LA DOCUMENTACIÓN**
