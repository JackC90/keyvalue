#!/bin/bash

php artisan migrate --force

# Start PHP-FPM
php-fpm