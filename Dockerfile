FROM php:8.2-apache

# 1. Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# 2. Install dependencies and PHP extensions only
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# 3. Ensure correct ownership for the web root
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
CMD ["apache2-foreground"]
