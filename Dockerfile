FROM php:8.2-apache

# Install build deps, the mongodb PECL extension, and enable Apache rewrite/headers
RUN apt-get update && apt-get install -y \
        libssl-dev \
        pkg-config \
        unzip \
        git \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html

# uploads/ must be writable by the web server user
RUN chown -R www-data:www-data /var/www/html/uploads

# Apache listens on $PORT for platforms like Render that assign it dynamically
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
ENV PORT=8080
EXPOSE 8081

CMD ["apache2-foreground"]
