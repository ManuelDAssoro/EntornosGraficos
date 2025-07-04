FROM php:8.2-cli

# Instala dependencias necesarias para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copia el código fuente
COPY . /var/www/html

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Expone el puerto (Render usará 80)
EXPOSE 80

# Inicia servidor embebido de PHP sirviendo desde /public
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
