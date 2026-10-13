# syntax=docker/dockerfile:1.7

# =============================================================================
# Stage: php-runtime
# Base layer for all other stages: PHP-FPM runtime, extensions, configs.
# Does NOT contain source code, dev tools, or Composer.
# =============================================================================
FROM php:8.4-fpm-trixie AS php-runtime

WORKDIR /var/www/html

# Runtime packages:
# - tzdata:         timezone data
# - libfcgi-bin:    cgi-fcgi utility for the health check
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    tzdata \
    libfcgi-bin \
 && rm -rf /var/lib/apt/lists/* \
 && mkdir -p /var/www/html \
 && chown -R www-data:www-data /var/www/html

# Install PHP extensions via mlocati/docker-php-extension-installer.
COPY --from=mlocati/php-extension-installer:2.12.0 /usr/bin/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions \
 && install-php-extensions \
    intl \
    opcache \
    pdo_pgsql \
    zip \
    redis \
    amqp \
 && rm -f /usr/local/bin/install-php-extensions

# Application-level configuration.
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf


# =============================================================================
# Stage: php-tooling
# Adds development/build tools on top of runtime.
# =============================================================================
FROM php-runtime AS php-tooling

ENV COMPOSER_HOME=/tmp/composer

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    bash \
    curl \
    git \
    zip \
    unzip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.10 /usr/bin/composer /usr/bin/composer


# =============================================================================
# Stage: dev
# Development image: Xdebug, aligned UID/GID, source is bind-mounted.
# =============================================================================
FROM php-tooling AS dev

ENV APP_ENV=dev \
    APP_DEBUG=1 \
    COMPOSER_HOME=/tmp/composer

ARG HOST_UID=1000
ARG HOST_GID=1000

# Xdebug (dev only).
COPY --from=mlocati/php-extension-installer:2.12.0 /usr/bin/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
 && install-php-extensions xdebug-3.5.3 \
 && rm -f /usr/local/bin/install-php-extensions

# Xdebug configuration (dev only).
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

# Align www-data UID/GID with the host user.
RUN groupmod -o -g "${HOST_GID}" www-data \
 && usermod -o -u "${HOST_UID}" -g "${HOST_GID}" www-data \
 && mkdir -p /tmp/composer \
 && chown -R www-data:www-data /tmp/composer

USER www-data

CMD ["php-fpm"]


# =============================================================================
# Stage: prod-dependencies
# Production-only Composer dependencies.
# =============================================================================
FROM php-tooling AS prod-dependencies

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY app/composer.json app/composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-progress \
    --no-interaction \
    --no-scripts


# =============================================================================
# Stage: build
# Adds source code, optimizes autoloader, warms up Symfony cache.
# =============================================================================
FROM prod-dependencies AS build

COPY app ./

RUN composer dump-autoload --classmap-authoritative --no-dev \
 && php bin/console cache:clear --env=prod --no-warmup \
 && php bin/console cache:warmup --env=prod


# =============================================================================
# Stage: prod
# Final production image: runtime + built application.
# =============================================================================
FROM php-runtime AS prod

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY --chown=www-data:www-data --from=build /var/www/html /var/www/html

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD SCRIPT_NAME=/fpm-ping SCRIPT_FILENAME=/fpm-ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q "pong" || exit 1

USER www-data

CMD ["php-fpm"]
