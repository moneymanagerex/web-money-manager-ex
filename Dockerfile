FROM php:8.0-apache
WORKDIR /var/www/html

COPY WebApp .
EXPOSE 80