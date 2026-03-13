FROM php:8.1-apache
WORKDIR /var/www/html

COPY WebApp .
RUN mv ./htaccess.txt ./.htaccess \
  && chown -R www-data:www-data /var/www/html

EXPOSE 80
