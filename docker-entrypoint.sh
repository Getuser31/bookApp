#!/bin/sh

# Install dependencies (volume mount overwrites build-time installations)
composer install --no-interaction --prefer-dist --no-scripts
npm install

# Start PHP-FPM in the background
php-fpm &

# Start the Vite development server
npm run dev
