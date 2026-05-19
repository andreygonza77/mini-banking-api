FROM php:7.4-apache

# Railway non usa UID/GID locali, impostiamo valori di fallback stabili
ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install unzip git -y
RUN docker-php-ext-install mysqli && a2enmod rewrite headers

# Configurazione CORS per Apache
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Headers "Content-Type"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "http:\/\/localhost:3000"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Credentials "true"\n\1\2/g' /etc/apache2/sites-available/*.conf

# Configura la porta di Apache per usare la variabile PORT di Railway
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/*.conf

# Copia il codice sorgente dentro l'immagine (sostituisce il vecchio volume)
COPY ./php /var/www/html

# Installazione Composer ed Entrypoint
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY entrypoint-php.sh /entrypoint-php.sh
RUN chmod +x /entrypoint-php.sh

RUN groupadd -f informatica -g $GID
RUN adduser --disabled-password --uid $UID --gid $GID --gecos "" informatica || true

# Espone la variabile PORT dinamica di Railway
EXPOSE ${PORT}

CMD ["/entrypoint-php.sh"]
