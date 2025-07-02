FROM php:8.2-cli

# Instala extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-install pgsql pdo_pgsql
RUN apt-get update && apt-get install -y libpq-dev


# Copia el código al contenedor
COPY . /var/www/html

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Expone el puerto 80
EXPOSE 80

# Comando para iniciar el servidor
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
