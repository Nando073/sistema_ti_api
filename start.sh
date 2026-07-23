#!/bin/bash
cd /var/www/html
php artisan config:clear
php artisan route:clear
php artisan view:clear
php -S 0.0.0.0:10000 -t public