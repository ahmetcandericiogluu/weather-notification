# PHP 8.3 image
FROM php:8.3-cli

# Sistem araçları ve composer kurulumu
RUN apt-get update && apt-get install -y git unzip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql pdo_pgsql zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Çalışma dizini
WORKDIR /app

# Tüm dosyaları kopyala
COPY . .

# Bağımlılıkları yükle
RUN composer install --no-dev --optimize-autoloader

# Symfony’nin public klasörünü servis et
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
