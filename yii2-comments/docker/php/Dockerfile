FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP configuration (path is relative to the build context root)
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Create runtime directories with proper permissions
RUN mkdir -p /var/www/html/frontend/runtime \
    /var/www/html/backend/runtime \
    /var/www/html/common/runtime \
    && chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]