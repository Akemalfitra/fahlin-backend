# Stage 1: Build Assets with Node.js
FROM node:20-alpine AS asset-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP Application with PHP-FPM
FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user=laravel
ARG uid=1000

# Install system dependencies (Ditambahkan libicu-dev untuk ekstensi intl)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (Ditambahkan intl di baris ini)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Salin file composer terlebih dahulu untuk optimasi cache layer Docker
COPY composer.json composer.lock ./

# Jalankan composer install sebagai root terlebih dahulu agar tidak terkendala permission cache folder
RUN composer install --no-interaction --no-plugins --no-scripts --optimize-autoloader --no-dev --prefer-dist

# Copy seluruh source code aplikasi
COPY . .

# Copy built assets dari Stage 1
COPY --from=asset-builder /app/public/build ./public/build

# Jalankan ulang untuk men-generate ulang autoloaders karena file aplikasi baru saja masuk
RUN composer dump-autoload --optimize --no-dev

# Set permissions agar folder storage & cache bisa ditulis oleh server
RUN chown -R $user:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

USER $user

EXPOSE 9000
CMD ["php-fpm"]