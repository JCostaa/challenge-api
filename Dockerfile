FROM php:8.2-fpm

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Antes de executar qualquer comando do composer
ENV COMPOSER_MEMORY_LIMIT=-1

# Configurar diretório de trabalho
WORKDIR /var/www

# Copiar arquivos de configuração e dependências
COPY composer.json composer.lock ./

# Instalar dependências - sem scripts, usando --no-interaction para evitar prompts
RUN composer install --no-scripts --no-autoloader --no-interaction

# Tentar instalar o pacote elasticsearch explicitamente
RUN composer require elasticsearch/elasticsearch:^8.0 --no-interaction || echo "Falha ao instalar elasticsearch/elasticsearch, mas continuando mesmo assim"

# Copiar o código da aplicação
COPY . .

# Gerar o autoloader otimizado
RUN composer dump-autoload --optimize

# Configurar permissões
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]