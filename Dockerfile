FROM php:7.4-apache

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install unzip git -y

# RISOLUZIONE BUG MPM: Installiamo mysqli, attiviamo i moduli e DISATTIVIAMO l'MPM extra che causa il crash
RUN docker-php-ext-install mysqli && \
    a2enmod rewrite headers && \
    a2dismod mpm_event || true

# Configurazione CORS per Apache
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Headers "Content-Type"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "http:\/\/localhost:3000"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Credentials "true"\n\1\2/g' /etc/apache2/sites-available/*.conf

# Fissiamo Apache sulla porta 8080 per Railway
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/g' /etc/apache2/sites-available/*.conf

# Copia il codice sorgente
COPY ./php /var/www/html

# Installazione Composer globale
RUN curl -sS https://getcomposer.org | php -- --install-dir=/usr/local/bin --filename=composer

# Installa le dipendenze se presente il file json
WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader --no-dev || true

# Assegniamo i permessi corretti alla cartella di Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

# Usiamo il comando nativo ufficiale (gestito correttamente da Railway)
CMD ["apache2-foreground"]
