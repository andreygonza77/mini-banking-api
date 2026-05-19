FROM php:7.4-apache

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install unzip git -y
RUN docker-php-ext-install mysqli && a2enmod rewrite headers

# Configurazione CORS per Apache
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Headers "Content-Type"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "http:\/\/localhost:3000"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Credentials "true"\n\1\2/g' /etc/apache2/sites-available/*.conf

# Fissiamo Apache sulla porta 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/g' /etc/apache2/sites-available/*.conf

# Copia il codice sorgente
COPY ./php /var/www/html

# Installazione Composer globale
RUN curl -sS https://getcomposer.org | php -- --install-dir=/usr/local/bin --filename=composer

# NUOVO: Esegui l'installazione dei pacchetti qui (ottimizzato per la build)
WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader --no-dev || true

# Configura l'entrypoint
COPY ./build/entrypoint-php.sh /entrypoint-php.sh
RUN chmod +x /entrypoint-php.sh

RUN groupadd -f informatica -g $GID
RUN adduser --disabled-password --uid $UID --gid $GID --gecos "" informatica || true

EXPOSE 8080

CMD ["/entrypoint-php.sh"]
