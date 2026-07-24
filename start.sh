#!/bin/bash
set -e

echo "=== Iniciando servidor con php artisan serve ==="
cd /var/www/html

# Limpiar caché (opcional, pero recomendado en producción)
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Iniciar servidor nativo de Laravel
php artisan serve --host=0.0.0.0 --port=10000