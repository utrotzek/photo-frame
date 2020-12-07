FROM php:8-apache

ARG USER_NAME=app
ARG UID=1000
ARG GID=1000

RUN groupadd -g $GID -o $USER_NAME && \
    useradd -m -u $UID -g $GID -o -s /bin/bash $USER_NAME

RUN apt-get update && \
    apt-get install -y \
        unzip \
        nodejs \
        npm

RUN a2dissite 000-default
RUN a2enmod rewrite

COPY resources/apache/ports.conf /etc/apache2/ports.conf
COPY resources/apache/site.conf /etc/apache2/sites-enabled/site.conf

COPY resources/php/composer.phar /usr/bin/composer

USER $USER_NAME
