#!/bin/sh

# Fix storage permissions for www-data (host-mounted volume resets ownership)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install dependencies (volume mount overwrites build-time installations)
composer install --no-interaction --prefer-dist --no-scripts
npm install

# Start PHP-FPM in the background
php-fpm &

# Start the Vite development server
npm run dev
