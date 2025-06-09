# Choose a PHP version compatible with your Laravel project
FROM php:8.4-fpm-alpine AS base

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk update && apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    vim \
    git \
    postgresql-dev \
    autoconf \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install exif \
    && docker-php-ext-install zip \
    && docker-php-ext-install opcache \
    && docker-php-ext-install pcntl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-custom.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Change current user to www
USER www

# Install composer dependencies
RUN composer install --optimize-autoloader

# Revert to root to change permissions and clear cache
USER root

# Set permissions for storage and bootstrap/cache
RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear caches
# RUN php artisan optimize:clear
# If you want to cache config and routes for production:
# RUN php artisan config:cache
# RUN php artisan route:cache
# RUN php artisan view:cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

# # Development stage (optional, if you want to run composer install with dev dependencies)
# FROM base AS development

# USER root
# RUN apk add --no-cache $PHPIZE_DEPS \
#     && pecl install xdebug && docker-php-ext-enable xdebug

# # Install dev composer dependencies
# USER www
# RUN composer install --optimize-autoloader --no-interaction --no-plugins --no-scripts

# USER root
# # Set permissions for storage and bootstrap/cache
# RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# # Clear caches
# RUN php artisan optimize:clear

# CMD ["php-fpm"]
