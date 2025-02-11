###################################################
# Alias for deppendencies
FROM node:22.12.0 AS node
FROM composer:2.8.4 AS composer
FROM php:8.4-fpm AS php-fpm
FROM mlocati/php-extension-installer:2.7.13 AS php-extension-installer
#FROM caddy:2.8.4 AS caddy

###################################################
################  FRONTEND STAGES  ################
###################################################

###################################################
FROM node AS frontend-base
WORKDIR /app/frontend
# install dependencies
COPY frontend/package.json frontend/package-lock.json \
    frontend/svelte.config.js \
    frontend/vite.config.ts \
    frontend/tsconfig.json /app/frontend/
# copy code
COPY frontend/src /app/frontend/src
COPY frontend/static /app/frontend/static
COPY shared /app/shared

###################################################
FROM frontend-base AS frontend-dev
RUN npm install
RUN if [ -d "src/design" ]; then cd src/design && npm link && cd ../.. && npm link @hyvor/design; fi
CMD npm run dev

###################################################
FROM frontend-base AS frontend-prod
# build the frontend
RUN  npm install \
    && npm run build \
    && find . -maxdepth 1 -not -name build -not -name . -exec rm -rf {} \;


###################################################
################  BACKEND STAGES  #################
###################################################


###################################################
FROM php-fpm AS backend-base

WORKDIR /app/backend

# install php and dependencies
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions zip intl pdo_pgsql


###################################################
FROM backend-base AS backend-dev

# symfony cli
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt install -y symfony-cli
# pcov for coverage
RUN install-php-extensions pcov
COPY backend/composer.json backend/composer.lock /app/backend/
RUN composer install --no-interaction
# set up code and install composer packages
COPY backend /app/backend/
# use local internal library if exists
RUN if [ -d "packages/internal" ]; then composer require hyvor/internal:@dev; fi
CMD symfony server:start --no-tls --listen-ip=0.0.0.0 --port=80

###################################################
# FROM backend-base AS final
# TODO
