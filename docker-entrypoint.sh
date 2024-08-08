#!/bin/sh

# Start PHP-FPM in the background
php-fpm &

# Start the Vite development server
npm run dev
