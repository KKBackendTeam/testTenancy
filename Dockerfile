# Use the official PHP image as the base image
FROM php:8.0-fpm

# Set working directory inside the container
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only Composer files for better caching
COPY composer.json composer.lock ./

# Install application dependencies using Composer
RUN composer install --no-scripts --no-autoloader

# Copy application code into the container
COPY . .

# Run any additional commands or scripts to prepare the application
RUN php artisan key:generate
RUN php artisan config:cache
RUN php artisan route:cache

# Expose port 9000 to communicate with PHP-FPM
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
