###################################################
# Alias for deppendencies
FROM node:23.11.0 AS node
FROM composer:2.8.8 AS composer
FROM dunglas/frankenphp:1.4.4-php8.4 AS frankenphp

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
CMD ["npm", "run", "dev"]

###################################################
FROM frontend-base AS frontend-prod
# build the frontend
RUN  npm install \
    && npm run build \
    && find . -maxdepth 1 -not -name build -not -name . -exec rm -rf {} \;



###################################################
################  ARCHIVE STAGES  ################
###################################################

###################################################
FROM node:22.17.1 AS archive-base
WORKDIR /app/archive
# install dependencies
COPY archive/package.json archive/package-lock.json \
    archive/svelte.config.js \
    archive/vite.config.ts \
    archive/tsconfig.json /app/archive/
# copy code
COPY archive/src /app/archive/src
COPY archive/static /app/archive/static
COPY shared /app/shared

###################################################
FROM archive-base AS archive-dev
RUN npm install
CMD ["npm", "run", "dev"]

###################################################
FROM archive-base AS archive-prod
# build the archive
RUN  npm install \
    && npm run build \
    && find . -maxdepth 1 \
        -not -name build \
        -not -name node_modules \
        -not -name package.json \
        -not -name . \
        -exec rm -rf {} \;



###################################################
################  EMBED STAGES  ################
###################################################

###################################################
FROM node AS embed-base
WORKDIR /app/embed
# install dependencies
COPY embed/package.json embed/package-lock.json \
    embed/vite.config.ts \
    embed/tsconfig.json \
    embed/src/ \
    /app/embed/

###################################################
FROM embed-base AS embed-dev
EXPOSE 80
RUN npm install
CMD ["npm", "run", "dev"]

###################################################
FROM embed-base AS embed-prod
# build the embed
RUN  npm install \
    && npm build \
    && find . -maxdepth 1 -not -name dist -not -name . -exec rm -rf {} \;



###################################################
################  BACKEND STAGES  #################
###################################################

###################################################
FROM frankenphp AS backend-base

WORKDIR /app/backend

# install php and dependencies
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
RUN install-php-extensions zip intl pdo_pgsql opcache apcu


###################################################
FROM backend-base AS backend-dev

ENV APP_RUNTIME="Runtime\FrankenPhpSymfony\Runtime"

# symfony cli
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt install -y symfony-cli
# pcov for coverage
RUN install-php-extensions pcov
COPY backend/composer.json backend/composer.lock /app/backend/
RUN composer install --no-interaction
# set up code and install composer packages
COPY backend /app/backend/
COPY meta/image/dev/Caddyfile.dev /etc/caddy/Caddyfile
COPY meta/image/dev/run.dev /app/run
CMD ["sh", "/app/run"]

###################################################
FROM backend-base AS final

ENV APP_RUNTIME="Runtime\FrankenPhpSymfony\Runtime"

RUN apt update && apt install -y supervisor

COPY backend /app/backend

RUN composer install --no-cache --prefer-dist --no-dev --no-scripts --no-progress

# Copy Frontend, Embed and Archive builds
COPY --from=frontend-prod /app/frontend/build /app/static
COPY --from=embed-prod /app/embed/dist /app/static/form
COPY --from=archive-prod /app/archive/ /app/archive

COPY meta/image/prod/Caddyfile.prod /etc/caddy/Caddyfile
COPY meta/image/prod/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY meta/image/prod/run.prod /app/run

EXPOSE 80
CMD ["sh", "/app/run"]
