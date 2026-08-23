FROM php:8.2-apache

# Install build dependencies, MongoDB extension, and enable Apache modules
RUN apt-get update && apt-get install -y \
        ca-certificates \
        openssl \
        libssl-dev \
        pkg-config \
        unzip \
        git \
    && update-ca-certificates \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html

# uploads/ must be writable by the web server user
RUN chown -R www-data:www-data /var/www/html/uploads

# Script to configure Apache's port at container startup
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]