FROM php:8.5-fpm-alpine AS vendor

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    libpq-dev

RUN docker-php-ext-install \
    intl \
    pdo_pgsql \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --no-autoloader \
    --no-scripts \
    --no-progress \
    --prefer-dist

FROM php:8.5-fpm-alpine AS production

RUN apk add --no-cache \
    curl \
    icu-data-full \
    icu-libs \
    libpq \
    libzip \
    && apk add --no-cache --virtual .build-deps \
    icu-dev \
    libpq-dev \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install intl pdo_pgsql zip \
    && apk del .build-deps

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APP_ENV=prod
RUN composer dump-autoload --classmap-authoritative --no-dev
RUN php bin/console cache:clear

RUN rm /usr/bin/composer

RUN chown -R www-data:www-data var

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

FROM php:8.5-fpm-alpine AS dev

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libpq-dev \
    libzip-dev \
    linux-headers

RUN docker-php-ext-install \
    intl \
    pdo_pgsql \
    zip

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

CMD ["php-fpm"]
