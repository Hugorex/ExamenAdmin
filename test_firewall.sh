#!/bin/bash

echo "=========================================="
echo "PRUEBAS DE FIREWALL - SISTEMA COMPLETO"
echo "=========================================="
echo ""

echo "[1/10] Estado UFW en todos los servidores..."
for server in ns ldap db www email; do
    echo "--- $server ---"
    vagrant ssh $server -c "sudo ufw status" 2>/dev/null | grep -E "Status:|22/tcp|53|389|3306|80/tcp|443/tcp|25/tcp|587/tcp|993/tcp"
    echo ""
done

echo "[2/10] Probando DNS (puerto 53) desde PC..."
dig @192.168.56.12 patitohosting.licic +short

echo ""
echo "[3/10] Probando LDAP desde WWW (debe funcionar)..."
vagrant ssh www -c "ldapsearch -x -H ldap://192.168.56.14 -b dc=patitohosting,dc=licic -D cn=admin,dc=patitohosting,dc=licic -w admin123 '(uid=jc)' uid 2>/dev/null" | grep "uid: jc"

echo ""
echo "[4/10] Probando MySQL desde WWW con wp_user (debe funcionar)..."
vagrant ssh www -c "mysql -h 192.168.56.11 -u wp_user -pwppassword -e 'SELECT 1 as test;' 2>/dev/null"

echo ""
echo "[5/10] Probando MySQL desde PC con admin (debe funcionar)..."
mysql -h 192.168.56.11 -u admin -padminpass123 -e "SELECT 'Conexión exitosa' as resultado;" 2>/dev/null

echo ""
echo "[6/10] Probando MySQL desde PC con wp_user (debe FALLAR - bloqueado por firewall)..."
mysql -h 192.168.56.11 -u wp_user -pwppassword -e "SELECT 1;" 2>&1 | grep -E "ERROR|Access denied" || echo "ERROR: No debería conectar"

echo ""
echo "[7/10] Probando HTTP (puerto 80)..."
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://192.168.56.10/wordpress/

echo ""
echo "[8/10] Probando SMTP puerto 25..."
timeout 3 bash -c "echo 'QUIT' | nc 192.168.56.13 25" 2>/dev/null | grep "220" && echo "✓ Puerto 25 abierto"

echo ""
echo "[9/10] Probando SMTP puerto 587..."
timeout 3 bash -c "echo 'QUIT' | nc 192.168.56.13 587" 2>/dev/null | grep "220" && echo "✓ Puerto 587 abierto"

echo ""
echo "[10/10] Probando IMAP 993 desde WWW (debe funcionar)..."
vagrant ssh www -c "timeout 2 bash -c 'echo quit | openssl s_client -connect 192.168.56.13:993 2>/dev/null' | grep -q 'Dovecot ready' && echo '✓ IMAP accesible desde WWW' || echo '✗ IMAP no accesible'"

echo ""
echo "=========================================="
echo "PRUEBAS COMPLETADAS"
echo "=========================================="
