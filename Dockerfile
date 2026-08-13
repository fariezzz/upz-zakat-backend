FROM php:8.2-fpm-alpine

# Install system dependencies and build packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    gettext \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    zip \
    unzip

# Install PHP extensions required by Laravel and PostgreSQL
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Copy Nginx template and supervisor config
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install Composer dependencies (production mode)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Set permissions for storage & cache, and make docker-run.sh executable
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chmod +x /var/www/docker-run.sh

# Render assigns dynamic PORT env variable
EXPOSE 8080

ENTRYPOINT ["/var/www/docker-run.sh"]
