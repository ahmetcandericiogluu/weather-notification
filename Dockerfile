FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl opcache pdo pdo_pgsql pdo_mysql zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

# ENV yüklenmeden sadece bağımlılıkları indir (no-scripts ekledik)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Symfony production ortamı
ENV APP_ENV=prod
ENV APP_DEBUG=0

CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
