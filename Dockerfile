FROM php:8.3-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        zip \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies (tanpa dev packages untuk production)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-dev

# Copy seluruh source code
COPY . .

# Set permissions untuk storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy dan set executable entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
