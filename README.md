# Infraestructura PatitoHosting - Vagrant + Ansible

Proyecto de automatización de infraestructura completa con 5 servidores Debian 12.

## 🚀 Quick Start

```bash
# Levantar toda la infraestructura
vagrant up

# Después del provisionamiento, aplicar parches necesarios
vagrant ssh db -c "sudo systemctl restart mariadb"
vagrant ssh email -c "sudo systemctl restart postfix"
```

## 📋 Requisitos

- **Vagrant** 2.4.x o superior
- **VirtualBox** 7.x o superior
- **Sistema Operativo**: Windows, Linux o macOS
- **RAM**: 16 GB mínimo
- **Disco**: 40 GB libres

**NOTA**: NO necesitas instalar Ansible en tu máquina. Se instala automáticamente en las VMs.

## 🏗️ Arquitectura

| Servidor | IP | Servicios |
|----------|-----|-----------|
| **www** | 192.168.56.10 | Apache, PHP, WordPress |
| **db** | 192.168.56.11 | MariaDB 10.11 |
| **ns** | 192.168.56.12 | BIND9 DNS |
| **email** | 192.168.56.13 | Postfix, Dovecot, Amavis |
| **ldap** | 192.168.56.14 | OpenLDAP |

## 📖 Documentación

- [DOCUMENTACION_COMPLETA.md](DOCUMENTACION_COMPLETA.md) - Documentación completa del proyecto
- [COMANDOS_RAPIDOS.md](COMANDOS_RAPIDOS.md) - Referencia rápida de comandos
- [PASOS_EJECUCION.md](PASOS_EJECUCION.md) - Guía paso a paso

## ✅ Características

- ✅ **Compatibilidad multiplataforma**: Windows, Linux, macOS
- ✅ **Automatización completa**: 1 comando para desplegar 5 servidores
- ✅ **Seguridad**: SSL/TLS, Firewall, LDAP centralizado
- ✅ **Alta disponibilidad**: Todos los servicios redundantes
- ✅ **Documentación completa**: Guías detalladas y troubleshooting

## 🛠️ Comandos Útiles

```bash
# Ver estado de las VMs
vagrant status

# Acceder a una VM
vagrant ssh www

# Detener todas las VMs
vagrant halt

# Eliminar todas las VMs
vagrant destroy

# Re-provisionar
vagrant provision
```

## 🔧 Solución de Problemas

### WordPress muestra HTTP 500
```bash
vagrant ssh db -c "sudo systemctl restart mariadb"
```

### Puerto 587 no accesible
```bash
vagrant ssh email -c "sudo systemctl restart postfix"
```

## 📦 Estructura del Proyecto

```
ExamenAdmin/
├── Vagrantfile              # Definición de VMs
├── ansible/
│   ├── site.yml            # Playbook principal
│   ├── ansible.cfg         # Configuración Ansible
│   ├── inventory/hosts     # Inventario de servidores
│   └── roles/              # Roles de Ansible
│       ├── common/
│       ├── dns/
│       ├── ldap/
│       ├── database/
│       ├── webserver/
│       └── mail/
└── docs/                   # Documentación adicional
```

## 📄 Licencia

Proyecto educativo - Administración de Sistemas
