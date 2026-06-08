# Stage 1: Build Assets with Node.js
FROM node:20-alpine AS asset-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP Application with PHP-FPM & Nginx
FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user=laravel
ARG uid=1000

# Install system dependencies (Ditambahkan nginx & supervisor)
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
    libicu-dev \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
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

# Salin konfigurasi Nginx & Supervisor ke dalam image
COPY nginx.conf /etc/nginx/nginx.conf

RUN echo '[supervisord]\n\
nodaemon=true\n\
user=laravel\n\
pidfile=/run/supervisord.pid\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n' > /etc/supervisor/conf.d/supervisord.conf

# Salin file composer terlebih dahulu untuk optimasi cache layer Docker
COPY composer.json composer.lock ./

# Jalankan composer install sebagai root terlebih dahulu agar tidak terkendala permission cache folder
RUN composer install --no-interaction --no-plugins --no-scripts --optimize-autoloader --no-dev --prefer-dist

# Copy seluruh source code aplikasi
COPY . .

# Copy built assets dari Stage 1
COPY --from=asset-builder /app/public/build ./public/build

# Jalankan ulang untuk men-generate ulang autoloaders
RUN composer dump-autoload --optimize --no-dev

# Set permissions agar folder aplikasi, nginx, dan supervisor bisa diakses user non-root
RUN chown -R $user:www-data /var/www /var/log/nginx /var/lib/nginx /run \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

USER $user

# Ekspos port 7860 untuk Hugging Face (Nginx listen ke port ini)
EXPOSE 7860

# Jalankan Nginx dan PHP-FPM bersamaan lewat Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]