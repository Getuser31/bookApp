FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    nodejs \
    npm \
    libzip-dev \
    zip \
    unzip \
    net-tools \
    lsof \
    # Install dependencies for adding PHP repositories
    ca-certificates \
    apt-transport-https \
    gnupg \
    lsb-release \
    ca-certificates \
    curl

# Update package lists
RUN apt-get update

# Install PECL extensions (including Xdebug)
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Install other PHP extensions
RUN docker-php-ext-install pdo_mysql zip

# Copy Xdebug configuration (customize as needed)
COPY docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

COPY php-fpm.conf /usr/local/etc/php/php-fpm.conf

# Set workdir
WORKDIR /var/www

# Copy project files
COPY . /var/www

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Composer dependencies
RUN composer install --no-interaction --prefer-dist --no-scripts

# Install Node.js dependencies
RUN npm install && npm run build

# Generate optimized autoloader files
RUN composer dump-autoload --optimize

# Expose port 9000 and start PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
