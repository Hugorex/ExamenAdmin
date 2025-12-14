# 🪟 GUÍA COMPLETA: DESPLEGAR EN WINDOWS

## 📋 REQUISITOS PREVIOS

### Software Necesario

1. **VirtualBox 7.x**
   - Descargar: https://www.virtualbox.org/wiki/Downloads
   - Instalar: `VirtualBox-7.x.x-Win.exe`
   - ✅ Verificar: Abrir VirtualBox Manager

2. **Vagrant 2.4.x**
   - Descargar: https://www.vagrantup.com/downloads
   - Instalar: `vagrant_2.4.x_windows_amd64.msi`
   - ✅ Verificar en PowerShell:
     ```powershell
     vagrant --version
     # Debe mostrar: Vagrant 2.4.x
     ```

3. **Recursos del Sistema**
   - RAM: Mínimo 8 GB (recomendado 16 GB)
   - Disco: 10 GB libres
   - CPU: Procesador con virtualización habilitada (Intel VT-x / AMD-V)

### ✅ Verificar Virtualización Habilitada

**Windows 10/11:**
1. Abrir "Administrador de tareas" (Ctrl+Shift+Esc)
2. Pestaña "Rendimiento" → "CPU"
3. Debe decir "Virtualización: Habilitada"

**Si está deshabilitada:**
- Reiniciar PC → Entrar a BIOS/UEFI (F2, F10, Del)
- Buscar "Intel VT-x" o "AMD-V" y habilitarlo
- Guardar y reiniciar

---

## 🚀 PASO 1: EXTRAER EL ARCHIVO ZIP

1. **Descargar** `ExamenAdmin_Portable.zip`

2. **Descomprimir** en una ruta SIN ESPACIOS:
   ```
   ✅ BIEN: C:\ExamenAdmin
   ✅ BIEN: D:\Proyectos\ExamenAdmin
   ❌ MAL:  C:\Mis Documentos\ExamenAdmin  (tiene espacio)
   ❌ MAL:  C:\Users\Juan Pablo\ExamenAdmin (tiene espacio)
   ```

3. **Verificar estructura:**
   ```
   C:\ExamenAdmin\
   ├── Vagrantfile
   ├── ansible\
   │   ├── site.yml
   │   └── roles\
   ├── README.md
   ├── COMANDOS_EVALUACION_PRESENCIAL.txt
   ├── CAMBIOS_ANSIBLE.md
   └── INSTRUCCIONES_WINDOWS.md (este archivo)
   ```

---

## 🎯 PASO 2: LEVANTAR LAS MÁQUINAS VIRTUALES

### ⚠️ IMPORTANTE: Este proceso tarda 15-20 minutos

1. **Abrir PowerShell normal** (NO necesitas administrador):
   - Presionar `Win + X`
   - Seleccionar "Windows PowerShell"
   - O buscar "PowerShell" en el menú Inicio

2. **Navegar a la carpeta:**
   ```powershell
   cd C:\ExamenAdmin
   ```

3. **Levantar las VMs:**
   ```powershell
   vagrant up
   ```

4. **ESPERAR SIN INTERRUMPIR** ⏳

   Verás mensajes como:
   ```
   ==> ns: Running provisioner: ansible_local...
   ==> ns: Installing Ansible...
   ==> ldap: Running provisioner: ansible_local...
   ==> db: Running provisioner: ansible_local...
   ==> email: Running provisioner: ansible_local...
   ==> www: Running provisioner: ansible_local...
   ```

   **ESTO ES NORMAL.** Cada VM está:
   - Instalando Ansible
   - Descargando paquetes (Apache, MariaDB, Postfix, etc.)
   - Configurando servicios
   - Instalando Roundcube (~15 MB)
   - Creando bases de datos

   **NO CANCELAR.** Tiempo estimado:
   - VM ns (DNS): 3-5 minutos
   - VM ldap: 4-6 minutos
   - VM db (MariaDB): 5-7 minutos
   - VM email (Postfix/Dovecot): 6-8 minutos
   - VM www (Apache/WordPress/Roundcube): 8-12 minutos
   
   **Total: 15-20 minutos**

5. **Cuando termine, verás:**
   ```
   ==> www: Machine 'www' has been provisioned!
   ```

---

## 🌐 PASO 3: CONFIGURAR HOSTS EN WINDOWS

**Para acceder a los servicios desde el navegador, necesitas editar el archivo hosts:**

1. **Abrir Notepad como Administrador:**
   - Buscar "Notepad" en el menú Inicio
   - Click derecho → "Ejecutar como administrador"

2. **Abrir el archivo hosts:**
   - Archivo → Abrir
   - Navegar a: `C:\Windows\System32\drivers\etc\`
   - Cambiar filtro a "Todos los archivos (*.*)"
   - Abrir el archivo llamado `hosts` (sin extensión)

3. **Agregar estas líneas al FINAL del archivo:**
   ```
   # PatitoHosting - Examen Administración
   192.168.56.10   www.patitohosting.licic patitohosting.licic
   192.168.56.10   webmail.patitohosting.licic
   192.168.56.11   db.patitohosting.licic
   192.168.56.12   ns.patitohosting.licic
   192.168.56.13   mail.patitohosting.licic
   192.168.56.14   ldap.patitohosting.licic
   ```

4. **Guardar** (Ctrl+S)

5. **Verificar en PowerShell:**
   ```powershell
   ping www.patitohosting.licic
   # Debe responder desde 192.168.56.10
   ```

---

## ✅ PASO 4: VALIDAR QUE TODO FUNCIONA

### 1️⃣ Verificar VMs en ejecución

```powershell
vagrant status
```

**Salida esperada:**
```
Current machine states:

ns                        running (virtualbox)
ldap                      running (virtualbox)
db                        running (virtualbox)
email                     running (virtualbox)
www                       running (virtualbox)
```

### 2️⃣ Probar servicios en navegador

**Abrir Chrome/Firefox y visitar:**

✅ **WordPress:** https://www.patitohosting.licic
   - Aceptar certificado autofirmado (Avanzado → Continuar)
   - Debe cargar WordPress

✅ **Webmail:** http://webmail.patitohosting.licic
   - Debe cargar Roundcube

### 3️⃣ Validar servicios desde PowerShell

```powershell
# DNS
vagrant ssh ns -c "systemctl status named"

# LDAP
vagrant ssh ldap -c "systemctl status slapd"

# MariaDB
vagrant ssh db -c "systemctl status mariadb"

# Postfix (Email)
vagrant ssh email -c "systemctl status postfix"

# Apache
vagrant ssh www -c "systemctl status apache2"
```

**Todos deben mostrar:** `Active: active (running)` ✅

---

## 🔐 PASO 5: CREDENCIALES DE ACCESO

### WordPress Admin
- URL: https://www.patitohosting.licic/wp-admin
- Usuario: `admin`
- Contraseña: `admin123`

### Webmail (Roundcube)
- URL: http://webmail.patitohosting.licic
- Usuarios disponibles:
  - `usuario1@patitohosting.licic` / `password123`
  - `usuario2@patitohosting.licic` / `password123`
  - `usuario3@patitohosting.licic` / `password123`
  - `wilo@patitohosting.licic` / `password123`

### Base de Datos (MariaDB)
- Host: `192.168.56.11`
- Usuario root: `root` / `root123`
- Base de datos WordPress: `wordpress_db`
- Base de datos Roundcube: `roundcube`

### SSH a las VMs
```powershell
# Conectar a cualquier VM
vagrant ssh www
vagrant ssh db
vagrant ssh email
vagrant ssh ldap
vagrant ssh ns

# Dentro de la VM, root sin contraseña:
sudo su -
```

---

## 📝 PASO 6: COMANDOS DE VALIDACIÓN (EVALUACIÓN)

**Copia y pega estos comandos en PowerShell para validar cada PASO:**

### PASO 1: Servidor Web
```powershell
vagrant ssh www -c "curl -k -I https://localhost | grep HTTP"
vagrant ssh www -c "systemctl status apache2 | grep Active"
vagrant ssh www -c "php -v | grep PHP"
```

### PASO 2: Base de Datos
```powershell
vagrant ssh db -c "mysql -uroot -proot123 -e 'SHOW DATABASES;' | grep wordpress_db"
vagrant ssh db -c "mysql -uroot -proot123 -e 'SELECT user, host FROM mysql.user;' | grep wordpress"
```

### PASO 3: DNS
```powershell
vagrant ssh ns -c "nslookup www.patitohosting.licic 127.0.0.1 | grep Address"
vagrant ssh ns -c "nslookup webmail.patitohosting.licic 127.0.0.1 | grep Address"
vagrant ssh ns -c "nslookup -type=MX patitohosting.licic 127.0.0.1"
```

### PASO 4: LDAP
```powershell
vagrant ssh ldap -c "ldapsearch -x -b 'dc=patitohosting,dc=licic' | grep dn:"
vagrant ssh ldap -c "ldapsearch -x -b 'dc=patitohosting,dc=licic' '(uid=usuario1)' | grep mail"
```

### PASO 5: Email (IMAP)
```powershell
vagrant ssh email -c "echo 'a1 LOGIN usuario1@patitohosting.licic password123' | nc localhost 143"
```

### PASO 6: Email (SMTP)
```powershell
vagrant ssh www -c @"
(echo 'EHLO test'; sleep 1; echo 'MAIL FROM:<test@test.com>'; sleep 1; echo 'RCPT TO:<usuario1@patitohosting.licic>'; sleep 1; echo 'DATA'; sleep 1; echo 'Subject: Test'; echo ''; echo 'Test message'; echo '.'; sleep 1; echo 'QUIT') | nc 192.168.56.13 25
"@
```

### PASO 7: Webmail
**Abrir navegador:**
1. http://webmail.patitohosting.licic
2. Login: `usuario1@patitohosting.licic` / `password123`
3. Enviar email a `usuario2@patitohosting.licic`
4. Login con usuario2 y verificar recepción

---

## 🛑 COMANDOS ÚTILES

### Detener VMs (liberar recursos)
```powershell
vagrant halt
```

### Reanudar VMs
```powershell
vagrant up
```

### Reiniciar una VM específica
```powershell
vagrant reload www
```

### Ver logs de provisioning
```powershell
vagrant up --debug
```

### Destruir todo y empezar de cero
```powershell
vagrant destroy -f
vagrant up
```

### Ver IP de las VMs
```powershell
vagrant ssh www -c "ip addr show eth1 | grep inet"
```

---

## 🐛 TROUBLESHOOTING

### ❌ Error: "VT-x is not available"
**Causa:** Virtualización deshabilitada en BIOS
**Solución:** Reiniciar → BIOS → Habilitar Intel VT-x / AMD-V

### ❌ Error: "Port 22 connection timeout"
**Causa:** VM no terminó de arrancar
**Solución:** Esperar 2-3 minutos y ejecutar `vagrant reload www`

### ❌ MariaDB no está instalado
**Causa:** Provisioning interrumpido
**Solución:**
```powershell
vagrant provision db
```

### ❌ Roundcube no carga
**Causa:** Provisioning de www no completó
**Solución:**
```powershell
vagrant provision www
# Esperar 10 minutos (descarga Roundcube)
```

### ❌ "No se puede conectar a 192.168.56.x"
**Causa:** Red VirtualBox no configurada
**Solución:**
```powershell
# Verificar en VirtualBox Manager:
# Archivo → Herramientas → Administrador de redes
# Debe existir "vboxnet0" con rango 192.168.56.0/24
```

### ❌ DNS no resuelve desde Windows
**Causa:** Archivo hosts mal configurado
**Solución:** Revisar `C:\Windows\System32\drivers\etc\hosts`

### ❌ Provisioning muy lento
**Normal en Windows.** Causas:
- Descarga de paquetes desde repositorios Debian
- Instalación de ~500 MB de software
- Roundcube descarga (15 MB)
- Antivirus escaneando archivos (desactivar temporalmente)

**Optimización:**
```powershell
# Deshabilitar Windows Defender temporalmente durante provisioning
# (Configuración → Virus → Protección en tiempo real → Desactivar)
```

---

## 📚 ARCHIVOS DE REFERENCIA

- `COMANDOS_EVALUACION_PRESENCIAL.txt` - Comandos para la evaluación
- `CAMBIOS_ANSIBLE.md` - Explicación de las configuraciones automatizadas
- `DOCUMENTACION_COMPLETA.md` - Arquitectura detallada
- `README.md` - Descripción del proyecto

---

## ⏱️ RESUMEN DE TIEMPOS

| Actividad | Tiempo Estimado |
|-----------|----------------|
| Instalar VirtualBox + Vagrant | 10 min |
| Extraer ZIP | 1 min |
| `vagrant up` (primera vez) | 15-20 min |
| Configurar hosts | 2 min |
| Validar servicios | 5 min |
| **TOTAL** | **~35 minutos** |

---

## ✅ CHECKLIST FINAL

Antes de presentar, verificar:

- [ ] 5 VMs en estado "running" (`vagrant status`)
- [ ] WordPress carga en navegador
- [ ] Roundcube carga en navegador
- [ ] Login en Roundcube con usuario1 funciona
- [ ] Enviar email desde Roundcube funciona
- [ ] DNS resuelve todos los dominios
- [ ] Todos los servicios activos (apache2, mariadb, postfix, dovecot, named, slapd)

**Si todo marca ✅ → LISTO PARA EVALUACIÓN** 🎉

### 📝 Resumen de Comandos

```powershell
# 1. Levantar infraestructura (ESPERAR 15-20 min)
vagrant up

# 2. Verificar que todo funciona
vagrant status

# 3. Aplicar parches
vagrant ssh db -c "sudo systemctl restart mariadb"
vagrant ssh email -c "sudo systemctl restart postfix"

# 4. Ejecutar pruebas (desde una VM)
vagrant ssh www -c "curl -k https://192.168.56.10"
```

### ✅ Checklist Final

- [ ] `vagrant up` completado sin errores
- [ ] Las 5 VMs en estado "running"
- [ ] MariaDB instalado: `vagrant ssh db -c "systemctl status mariadb"`
- [ ] WordPress accesible: abrir navegador en `https://192.168.56.10`
- [ ] Parches aplicados (restart mariadb y postfix)

---

**Fecha:** 11 de diciembre de 2025  
**Compatible con:** Windows 10/11, PowerShell 5.x+
