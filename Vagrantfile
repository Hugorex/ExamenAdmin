# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # Configuración global
  config.vm.box = "generic/debian12"
  config.vm.box_check_update = false
  
  # Habilitar sincronización de carpetas (necesario para ansible_local)
  config.vm.synced_folder ".", "/vagrant", type: "virtualbox"
  
  # Configuración de red privada
  private_network = "192.168.56"
  
  # ============================================
  # SERVIDOR DNS (debe ser el primero en levantarse)
  # ============================================
  config.vm.define "ns", primary: true do |ns|
    ns.vm.hostname = "ns.patitohosting.licic"
    ns.vm.network "private_network", ip: "#{private_network}.12"
    
    ns.vm.provider "virtualbox" do |vb|
      vb.name = "ns.patitohosting.licic"
      vb.memory = 512
      vb.cpus = 1
    end
    
    ns.vm.provision "shell", inline: <<-SHELL
      sudo ssh-keygen -A
      sudo systemctl restart ssh
    SHELL
  end
  
  # ============================================
  # SERVIDOR LDAP
  # ============================================
  config.vm.define "ldap" do |ldap|
    ldap.vm.hostname = "ldap.patitohosting.licic"
    ldap.vm.network "private_network", ip: "#{private_network}.14"
    
    ldap.vm.provider "virtualbox" do |vb|
      vb.name = "ldap.patitohosting.licic"
      vb.memory = 1024
      vb.cpus = 1
    end
    
    ldap.vm.provision "shell", inline: <<-SHELL
      sudo ssh-keygen -A
      sudo systemctl restart ssh
    SHELL
  end
  
  # ============================================
  # SERVIDOR DE BASE DE DATOS
  # ============================================
  config.vm.define "db" do |db|
    db.vm.hostname = "db.patitohosting.licic"
    db.vm.network "private_network", ip: "#{private_network}.11"
    
    db.vm.provider "virtualbox" do |vb|
      vb.name = "db.patitohosting.licic"
      vb.memory = 1024
      vb.cpus = 1
    end
    
    db.vm.provision "shell", inline: <<-SHELL
      sudo ssh-keygen -A
      sudo systemctl restart ssh
    SHELL
  end
  
  # ============================================
  # SERVIDOR WEB
  # ============================================
  config.vm.define "www" do |www|
    www.vm.hostname = "www.patitohosting.licic"
    www.vm.network "private_network", ip: "#{private_network}.10"
    
    www.vm.provider "virtualbox" do |vb|
      vb.name = "www.patitohosting.licic"
      vb.memory = 2048
      vb.cpus = 2
    end
    
    www.vm.provision "shell", inline: <<-SHELL
      sudo ssh-keygen -A
      sudo systemctl restart ssh
    SHELL
  end
  
  # ============================================
  # SERVIDOR DE CORREO (último en provisionarse)
  # ============================================
  config.vm.define "email" do |email|
    email.vm.hostname = "email.patitohosting.licic"
    email.vm.network "private_network", ip: "#{private_network}.13"
    
    email.vm.provider "virtualbox" do |vb|
      vb.name = "email.patitohosting.licic"
      vb.memory = 2048
      vb.cpus = 2
    end
    
    email.vm.provision "shell", inline: <<-SHELL
      sudo ssh-keygen -A
      sudo systemctl restart ssh
    SHELL
    
    # Provisión con Ansible Local (compatible con Windows, macOS y Linux)
    # Ansible se ejecuta DENTRO de las VMs, no desde el host
    email.vm.provision "ansible_local" do |ansible|
      ansible.limit = "all"
      ansible.playbook = "ansible/site.yml"
      ansible.inventory_path = "ansible/inventory/hosts"
      ansible.verbose = "v"
      ansible.install_mode = "default"
      ansible.install = true
      ansible.extra_vars = {
        ansible_python_interpreter: "/usr/bin/python3"
      }
    end
  end
end
