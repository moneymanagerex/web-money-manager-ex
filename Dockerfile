FROM php:8.1-apache
WORKDIR /var/www/html

COPY WebApp .
RUN mv ./htaccess.txt ./.htaccess \
  && chown -R www-data:www-data /var/www/html \
  && mkdir /data \
  && chown -R www-data:www-data /data \
  && sed -i 's#MMEX_New_Transaction.db#/data/MMEX_New_Transaction.db#' /var/www/html/configuration_system.php

VOLUME /data
EXPOSE 80
