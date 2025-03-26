FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


ENV COMPOSER_MEMORY_LIMIT=-1


WORKDIR /var/www


COPY composer.json composer.lock ./


RUN composer install --no-scripts --no-autoloader --no-interaction


RUN composer require elasticsearch/elasticsearch:^8.0 --no-interaction || echo "Falha ao instalar elasticsearch/elasticsearch, mas continuando mesmo assim"


COPY . .


RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]