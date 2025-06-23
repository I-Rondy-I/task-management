FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    netcat-openbsd \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --optimize-autoloader --no-scripts --ignore-platform-reqs

COPY . .

RUN mkdir -p var && chown -R www-data:www-data var

CMD ["php", "-S", "0.0.0.0:8022", "-t", "public"]
