#!/bin/bash

PORT="${PORT:-80}"

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "Running MongoDB database setup..."
php /var/www/html/db_setup.php || echo "Database setup failed; continuing with Apache startup."

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground