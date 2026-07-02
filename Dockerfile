FROM php:8.2-apache

# 1. Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# 2. Install dependencies, PHP extensions, and cron
RUN apt-get update && apt-get install -y \
    cron \
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

# 4. Copy cron file and configure it
COPY service-auditor.cron /etc/cron.d/service-auditor
RUN chmod 0644 /etc/cron.d/service-auditor \
    && touch /var/log/service-auditor-cron.log

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
