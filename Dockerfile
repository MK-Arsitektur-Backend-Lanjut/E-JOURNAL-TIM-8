FROM php:8.2-fpm

# ── System Dependencies ────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Redis Extension (untuk Cache & Queue) ─────────────────────────────────
RUN pecl install redis && docker-php-ext-enable redis

# ── Composer ──────────────────────────────────────────────────────────────
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ── Working Directory ──────────────────────────────────────────────────────
WORKDIR /var/www/html

# ── Copy Project ───────────────────────────────────────────────────────────
COPY . .

# ── Install PHP Dependencies ───────────────────────────────────────────────
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ── Storage Permission ─────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
