# ============================================================
# Stage 1: Build — install dependencies with all extensions
# ============================================================
FROM php:8.3-fpm AS builder

# System packages required to compile intl and zip extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        zip \
        unzip \
        git \
        curl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        zip \
        pdo \
        pdo_mysql \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer from its official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy dependency manifests first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev, no scripts — app code not present yet)
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# Copy the rest of the application
COPY . .

# Run post-install scripts now that the full app is present
RUN composer run-script post-autoload-dump --no-interaction || true

# ============================================================
# Stage 2: Production image
# ============================================================
FROM php:8.3-fpm AS production

# Same system libraries needed at runtime (intl, zip)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        nginx \
        supervisor \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        zip \
        pdo \
        pdo_mysql \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Copy built application from the builder stage
COPY --from=builder /var/www/html /var/www/html

# Set correct ownership for Laravel's writable directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Nginx configuration — serves the Laravel public directory
RUN printf 'server {\n\
    listen 80;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \\.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
\n\
    location ~ /\\.ht {\n\
        deny all;\n\
    }\n\
}\n' > /etc/nginx/sites-available/default

# Supervisor configuration — runs nginx + php-fpm together
RUN printf '[supervisord]\n\
nodaemon=true\n\
user=root\n\
logfile=/var/log/supervisor/supervisord.log\n\
pidfile=/var/run/supervisord.pid\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
stderr_logfile=/var/log/nginx/error.log\n\
stdout_logfile=/var/log/nginx/access.log\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
stderr_logfile=/var/log/php-fpm/error.log\n\
stdout_logfile=/var/log/php-fpm/access.log\n' > /etc/supervisor/conf.d/laravel.conf

RUN mkdir -p /var/log/supervisor /var/log/nginx /var/log/php-fpm

EXPOSE 80

# Bootstrap the app then hand off to supervisor
CMD ["/bin/sh", "-c", "\
    cp -n /var/www/html/.env.example /var/www/html/.env && \
    php artisan key:generate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    exec supervisord -c /etc/supervisor/supervisord.conf \
"]
