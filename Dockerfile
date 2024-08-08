FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
     git \
     nodejs \
     npm \
    libzip-dev zip unzip net-tools lsof && \
    docker-php-ext-install pdo_mysql zip && \
    rm -rf /var/lib/apt/lists/*

# Install Composer
RUN apt-get install -y curl && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Set the working directory
WORKDIR /var/www

# Copy the application files into the container
COPY . /var/www

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --no-scripts

# Install NPM dependencies and build the frontend assets
RUN npm install && npm run build

# Install composer dependencies
RUN composer dump-autoload --optimize

# Expose port 9000 and start PHP-FPM server
EXPOSE 9000

