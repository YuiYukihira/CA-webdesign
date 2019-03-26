# See https://hub.docker.com/_/php/
FROM php:7.3.3-apache

# Copy source code into /var/www/html inside the container
COPY ./db_conn.php /var/www/
COPY ./src /var/www/html/
