#syntax=docker/dockerfile:1

FROM dunglas/frankenphp:1-php8.4 AS frankenphp_upstream

FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        file \
        git \
        libpq-dev \
    && install-php-extensions \
        @composer \
        intl \
        opcache \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY frankenphp/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]
HEALTHCHECK --start-period=60s CMD php -r 'exit(false === @file_get_contents("http://localhost:2019/metrics", context: stream_context_create(["http" => ["timeout" => 5]])) ? 1 : 0);'
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]

FROM frankenphp_base AS dev

ENV APP_ENV=dev
ENV FRANKENPHP_WORKER_CONFIG=watch

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch"]

FROM frankenphp_base AS prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --prefer-dist --no-interaction

COPY . .

RUN composer dump-autoload --classmap-authoritative --no-dev \
    && APP_SECRET=build php bin/console assets:install public --no-interaction \
    && APP_SECRET=build php bin/console importmap:install --no-interaction \
    && APP_SECRET=build php bin/console cache:clear --env=prod --no-warmup \
    && mkdir -p var/cache var/log var/share \
    && chmod -R g=u var

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
