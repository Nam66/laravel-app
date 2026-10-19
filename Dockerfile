FROM php:8.3-fpm

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libpq-dev libzip-dev unzip p7zip-full \
	&& pecl install redis \
	&& docker-php-ext-enable redis \
	&& docker-php-ext-install pdo_mysql pdo_pgsql bcmath zip \
	&& rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
