FROM php:8.3-apache

# pdo_mysql é a única extensão além do core que a aplicação usa.
RUN docker-php-ext-install pdo_mysql

# A raiz do servidor aponta para public/ para que src/, views/ e database/
# fiquem fora do alcance do navegador.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
      /etc/apache2/sites-available/*.conf \
      /etc/apache2/apache2.conf

WORKDIR /var/www/html
