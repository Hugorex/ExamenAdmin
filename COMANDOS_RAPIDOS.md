# COMANDOS RÁPIDOS - Infraestructura PatitoHosting

## 🚀 Gestión Básica

```bash
# Levantar todo
vagrant up

# Ver estado
vagrant status

# Detener
vagrant halt

# Acceder a VM
vagrant ssh www
vagrant ssh db
vagrant ssh email
```

## 🔧 Parches Necesarios

```bash
# IMPORTANTE: Ejecutar después de vagrant up
vagrant ssh db -c "sudo systemctl restart mariadb"
vagrant ssh email -c "sudo systemctl restart postfix"
```

## ✅ Verificación

```bash
# Probar puertos (Linux/macOS)
nc -zv 192.168.56.10 80 443
nc -zv 192.168.56.11 3306
nc -zv 192.168.56.13 25 587

# WordPress
curl -Ik https://192.168.56.10/
```

## 📦 Empaquetado

```bash
# Crear ZIP
cd ~
zip -r ExamenAdmin.zip ExamenAdmin/ \
  -x "ExamenAdmin/.vagrant/*" \
  -x "ExamenAdmin/*.log"
```

## 🔑 Credenciales

- **MariaDB Root**: root / rootpass123
- **WordPress DB**: wp_user / wppass123
- **LDAP Admin**: cn=admin,dc=patitohosting,dc=licic / ldapadmin123
