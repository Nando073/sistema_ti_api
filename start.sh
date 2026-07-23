#!/bin/bash

# Limpiar cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cachear configuraciones
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar el servidor
php artisan serve --host=0.0.0.0 --port=10000