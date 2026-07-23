#!/bin/bash
set -e

echo "=== Iniciando servidor PHP con router ==="
cd /var/www/html

# Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Iniciar servidor con public/index.php como router
php -S 0.0.0.0:10000 -t public public/index.php