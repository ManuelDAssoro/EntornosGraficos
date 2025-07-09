FROM php:8.2-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    zip \
    git \
    && docker-php-ext-install pdo_pgsql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar configuración personalizada
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Copiar proyecto completo
COPY . /var/www/html

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html
