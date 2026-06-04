FROM php:8.3-fpm

# 1. Install Node.js & NPM (Versi stabil 20.x)
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# 2. Install System Dependencies + Redis Extension
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Folder Kerja (Tetap /var/www agar Nginx tidak rusak)
WORKDIR /var/www

# 5. Optimasi Install Composer
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 6. Copy seluruh project
COPY . .

# 7. Build Frontend (Otomatis)
RUN npm install && npm run build

# 8. Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
