# 🆘 TROUBLESHOOTING COMPLETO

## ⚠️ PROBLEMA: "Los servicios no existen"

### 🔍 DIAGNÓSTICO

**Paso 1: Verificar estado de VMs**
```powershell
cd C:\ExamenAdmin
vagrant status
```

**Salida esperada:**
```
ns                        running (virtualbox)
ldap                      running (virtualbox)
db                        running (virtualbox)
email                     running (virtualbox)
www                       running (virtualbox)
```

Si alguna dice `not created` o `poweroff` → El provisioning no terminó.

---

**Paso 2: Verificar si servicios están instalados**
```powershell
# Probar MariaDB
vagrant ssh db -c "systemctl status mariadb 2>&1"

# Probar Apache
vagrant ssh www -c "systemctl status apache2 2>&1"

# Probar Postfix
vagrant ssh email -c "systemctl status postfix 2>&1"
```

### 📊 INTERPRETACIÓN DE RESULTADOS

| Salida | Significado | Solución |
|--------|-------------|----------|
| `Active: active (running)` | ✅ Servicio OK | Continuar validación |
| `Active: inactive (dead)` | ⚠️ Servicio instalado pero detenido | Ejecutar `systemctl start` |
| `Unit X.service could not be found` | ❌ Servicio NO instalado | **Forzar provisioning** |
| `Failed to connect to bus` | ❌ systemd no disponible | **Recrear VM** |

---

## 🔧 SOLUCIÓN 1: Forzar Provisioning (Más Común)

Si los servicios **NO existen**, el provisioning no se ejecutó.

### Windows PowerShell:
```powershell
cd C:\ExamenAdmin

# Forzar provisioning en TODAS las VMs (puede tardar 20 minutos)
vagrant provision

# O solo en VMs específicas:
vagrant provision db
vagrant provision www
vagrant provision email
```

**⏱️ Tiempo estimado por VM:**
- `db` (MariaDB): 5-7 minutos
- `www` (Apache + WordPress + Roundcube): 10-15 minutos
- `email` (Postfix + Dovecot): 6-8 minutos
- `ldap`: 4-6 minutos
- `ns` (DNS): 3-5 minutos

**✅ Cuando termine, volver a probar:**
```powershell
vagrant ssh db -c "systemctl status mariadb"
```

---

## 🔧 SOLUCIÓN 2: Destruir y Recrear (Si provisioning falla)

```powershell
cd C:\ExamenAdmin

# Destruir TODAS las VMs
vagrant destroy -f

# Recrear desde cero (esperar 20 minutos SIN CANCELAR)
vagrant up
```

**⚠️ IMPORTANTE:** 
- NO interrumpir el proceso
- NO cerrar PowerShell
- Esperar a ver el mensaje final: "Machine 'www' has been provisioned!"

---

## 🔧 SOLUCIÓN 3: Actualizar VirtualBox/Vagrant

### Verificar versiones actuales:
```powershell
# Ver versión de Vagrant
vagrant --version

# Ver versión de VirtualBox
"C:\Program Files\Oracle\VirtualBox\VBoxManage.exe" --version
```

### Versiones recomendadas:
- **VirtualBox**: 7.0.x o superior
- **Vagrant**: 2.4.x o superior

### Si están desactualizados:

**VirtualBox:**
1. Descargar: https://www.virtualbox.org/wiki/Downloads
2. Instalar `VirtualBox-7.x.x-Win.exe`
3. Reiniciar Windows

**Vagrant:**
1. Descargar: https://www.vagrantup.com/downloads
2. Instalar `vagrant_2.4.x_windows_amd64.msi`
3. Reiniciar PowerShell

**Después de actualizar:**
```powershell
cd C:\ExamenAdmin
vagrant destroy -f
vagrant up
```

---

## 🔧 SOLUCIÓN 4: Verificar Virtualización Habilitada

### Windows 10/11:
1. Abrir "Administrador de tareas" (Ctrl+Shift+Esc)
2. Pestaña "Rendimiento"
3. Click en "CPU"
4. Buscar: **"Virtualización: Habilitada"**

### Si dice "Deshabilitada":
1. Reiniciar PC
2. Entrar a BIOS/UEFI (presionar F2, F10, Del o Esc durante arranque)
3. Buscar opción:
   - Intel: "Intel VT-x" o "Virtualization Technology"
   - AMD: "AMD-V" o "SVM Mode"
4. Cambiar a **Enabled**
5. Guardar y salir (F10)
6. Reiniciar
7. Volver a intentar `vagrant up`

---

## 🔧 SOLUCIÓN 5: Problemas de Red VirtualBox

Si las VMs arrancan pero no tienen red:

```powershell
# Ver configuración de red
vagrant ssh www -c "ip addr show eth1"
```

**Debería mostrar:**
```
inet 192.168.56.10/24
```

### Si no tiene IP 192.168.56.x:

**En VirtualBox Manager:**
1. Archivo → Herramientas → Administrador de redes
2. Verificar que existe "vboxnet0" o red host-only
3. Rango debe ser: `192.168.56.0/24`
4. DHCP deshabilitado

**Si no existe:**
```powershell
# Recrear red host-only
VBoxManage hostonlyif create
VBoxManage hostonlyif ipconfig vboxnet0 --ip 192.168.56.1 --netmask 255.255.255.0
```

**Luego:**
```powershell
vagrant reload
```

---

## 🔧 SOLUCIÓN 6: Ver Logs de Provisioning

Si el provisioning falla, ver detalles:

```powershell
# Ver logs completos
vagrant up --debug 2>&1 | Out-File -FilePath provisioning.log

# Revisar el archivo provisioning.log
notepad provisioning.log
```

Buscar errores que contengan:
- `FAILED`
- `ERROR`
- `fatal:`
- `unreachable:`

---

## 🔧 SOLUCIÓN 7: Verificar Recursos del Sistema

### Requisitos mínimos:
- **RAM**: 8 GB (las 5 VMs usan ~6 GB)
- **CPU**: 4 núcleos
- **Disco**: 10 GB libres

### Verificar RAM disponible:
```powershell
# Ver memoria disponible
Get-CimInstance Win32_OperatingSystem | Select-Object FreePhysicalMemory
```

Si tiene menos de 4 GB libres:
1. Cerrar aplicaciones pesadas
2. Reducir VMs (levantar solo las necesarias):
   ```powershell
   vagrant up ns ldap db  # Solo infraestructura básica
   ```

---

## 🔧 SOLUCIÓN 8: Antivirus Bloqueando

**Windows Defender o antivirus de terceros pueden bloquear Ansible.**

### Desactivar temporalmente durante provisioning:

**Windows Defender:**
1. Configuración → Actualización y seguridad → Seguridad de Windows
2. Protección contra virus y amenazas
3. Administrar configuración
4. Desactivar "Protección en tiempo real"
5. Ejecutar `vagrant up`
6. Reactivar protección después

---

## 📋 CHECKLIST DE VALIDACIÓN

Después de aplicar soluciones, verificar:

```powershell
# 1. VMs corriendo
vagrant status
# Todas deben decir "running"

# 2. Servicios instalados y activos
vagrant ssh db -c "systemctl is-active mariadb"
vagrant ssh www -c "systemctl is-active apache2"
vagrant ssh email -c "systemctl is-active postfix dovecot"
vagrant ssh ldap -c "systemctl is-active slapd"
vagrant ssh ns -c "systemctl is-active named"
# Todas deben responder "active"

# 3. Red funcionando
ping 192.168.56.10
ping 192.168.56.11
ping 192.168.56.13
# Todas deben responder

# 4. WordPress accesible
curl -k https://www.patitohosting.licic
# Debe devolver HTML

# 5. Webmail accesible
curl http://webmail.patitohosting.licic
# Debe devolver HTML
```

**Si TODOS marcan ✅ → PROBLEMA RESUELTO**

---

## ✅ RESUMEN DE COMANDOS ÚTILES

```powershell
# Ver todo el estado
vagrant global-status

# Recargar configuración de red
vagrant reload

# Forzar provisioning
vagrant provision

# Recrear desde cero
vagrant destroy -f && vagrant up

# Entrar a VM para debug
vagrant ssh www
sudo journalctl -xe  # Ver logs del sistema
sudo systemctl list-units --failed  # Ver servicios fallidos

# Liberar recursos (detener VMs)
vagrant halt

# Verificar conectividad SSH
vagrant ssh-config
```
