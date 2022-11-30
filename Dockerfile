FROM php:8.1-apache
WORKDIR /var/www/html

COPY WebApp .
EXPOSE 80
