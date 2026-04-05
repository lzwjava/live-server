FROM php:8.5-fpm

# Install additional system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    ffmpeg \
    && rm -rf /var/lib/apt/lists/*

# Configure GD with jpeg and freetype support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install only extensions that aren't in the base image
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    zip \
    intl \
    exif \
    pcntl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Install CodeIgniter 4 framework
RUN composer install --no-dev --prefer-dist --ignore-platform-reqs --no-interaction --no-progress || true

# Create writable directories
RUN mkdir -p /var/www/html/writable/cache \
             /var/www/html/writable/logs \
             /var/www/html/writable/session \
             /var/www/html/writable/uploads \
             /var/www/html/writable/debugbar \
             && chmod -R 777 /var/www/html/writable

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]