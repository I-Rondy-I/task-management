FROM php:8.2-cli

# Instalacja zależności systemowych
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

# Instalacja Composera
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ustawienie katalogu roboczego
WORKDIR /app

# Najpierw kopiujemy pliki composera i instalujemy zależności
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --optimize-autoloader --no-scripts --ignore-platform-reqs

# Następnie kopiujemy całą aplikację (na końcu)
COPY . .

# Nadanie uprawnień Symfony (jeśli katalogi istnieją)
RUN mkdir -p var && chown -R www-data:www-data var

# Domyślna komenda (i tak zostanie nadpisana przez docker-compose)
CMD ["php", "-S", "0.0.0.0:8022", "-t", "public"]
