FROM php:8.3-apache
WORKDIR /var/www/html

ENV WEBMMX_DB_FILE=/data/MMEX_New_Transaction.db
ENV WEBMMX_ATTACHMENTS_FOLDER=/data/attachments
ENV WEBMMX_USER_CONFIG_FILE=/data/configuration_user.php

COPY WebApp .
RUN mv ./htaccess.txt ./.htaccess \
  && mv ./attachments/htaccess.txt ./attachments/.htaccess \
  && chown -R www-data:www-data /var/www/html \
  && mkdir /data \
  && chown -R www-data:www-data /data

VOLUME /data
EXPOSE 80
