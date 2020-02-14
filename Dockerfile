FROM composer:latest
RUN apk upgrade --update && apk add \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
WORKDIR /beep
COPY . /beep
RUN ls -l && composer install && mv storage storage.bak && chmod -R 777 bootstrap/cache



FROM php:7.4-apache
WORKDIR /var/www/html/
COPY --from=0 /beep/ .
COPY apache/docker.conf /etc/apache2/sites-enabled/
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        netcat \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
    /etc/apache2/apache2.conf


ENTRYPOINT [ "./docker-run.sh" ]
