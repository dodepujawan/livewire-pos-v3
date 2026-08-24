FROM php:8.4.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql zip gd mbstring exif pcntl bcmath intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Versi lama php last not spesific
# # FROM php:8.3-fpm
# # alternative fix version saat di develop

# # Install dependencies
# RUN apt-get update && apt-get install -y \
#     libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev \
#     && docker-php-ext-install pdo_mysql zip gd mbstring exif pcntl bcmath

# # Install Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# WORKDIR /var/www/html
