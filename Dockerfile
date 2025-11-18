# Dockerfile (à la racine du projet)
FROM php:8.3-fpm

# Installer dépendances
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libicu-dev libpq-dev libssl-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mbstring zip intl sockets pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Dossier de travail (dans le conteneur)
WORKDIR /var/www/html

# Copier uniquement composer.* au début (meilleur cache)
COPY webapp/composer.json webapp/composer.lock ./

RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

# Copier l'application Laravel
COPY webapp/ ./

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]