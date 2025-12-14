# PASOS DE EJECUCIÓN - Infraestructura PatitoHosting

## 📋 Requisitos Previos

1. **Vagrant** 2.4.x instalado
2. **VirtualBox** 7.x instalado
3. **16 GB RAM** mínimo
4. **40 GB disco** libre

## 🚀 Pasos de Instalación

### PASO 1: Extraer proyecto
```bash
cd ~/
unzip ExamenAdmin.zip
cd ExamenAdmin
```

### PASO 2: Levantar infraestructura
```bash
vagrant up
```
⏱️ Tiempo: 15-20 minutos

### PASO 3: Aplicar parches
```bash
vagrant ssh db -c "sudo systemctl restart mariadb"
vagrant ssh email -c "sudo systemctl restart postfix"
```

### PASO 4: Verificar
```bash
vagrant status
```

Todos deben mostrar `running (virtualbox)`

## ✅ Pruebas

```bash
# Conectividad
ping 192.168.56.10
ping 192.168.56.11
ping 192.168.56.12
ping 192.168.56.13
ping 192.168.56.14

# WordPress
curl -Ik https://192.168.56.10/
# Debe responder HTTP 302 o 200

# Puertos
nc -zv 192.168.56.13 587
# Debe mostrar: Connection succeeded
```

## 🛑 Detener Infraestructura

```bash
vagrant halt
```

## 🗑️ Eliminar Todo

```bash
vagrant destroy -f
```

## 📖 Más Información

Ver [DOCUMENTACION_COMPLETA.md](DOCUMENTACION_COMPLETA.md)
