FROM php:7.4-fpm-alpine
LABEL maintainer="marcus.haase@milchundzucker.de" \
        containermode="development"
RUN apk add --virtual .deps autoconf curl libzip-dev libxml2-dev zlib-dev freetype-dev jpeg-dev libpng-dev \
            build-base icu-dev gmp-dev libxslt-dev
RUN pecl install xdebug
RUN docker-php-ext-install zip xml json intl gd iconv sockets pdo_mysql gmp soap mysqli xsl
RUN docker-php-ext-enable xdebug
RUN curl -s https://getcomposer.org/composer-stable.phar > /bin/composer &&\
    chmod a+x /bin/composer &&\
    ln -s /bin/composer /usr/local/bin/composer &&\
    ln -s /bin/composer /usr/local/composer &&\
    ln -s /bin/composer /usr/bin/composer &&\
    apk del .deps &&\
    echo 'Europe/Berlin' > /etc/timezone &&\
    echo 'date.timezone="Europe/Berlin"' > ${PHP_INI_DIR}/conf.d/date_timezone.ini &&\
    apk add --no-cache libzip libxml2 libpng zlib freetype jpeg icu gmp subversion libxslt unzip git yarn
EXPOSE 9000
