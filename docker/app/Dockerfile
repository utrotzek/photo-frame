ARG PHP_VERSION=7.4
ARG UBUNTU_VERSION=20.04
FROM ubuntu:$UBUNTU_VERSION

ARG DEBIAN_FRONTEND=noninteractive
ARG USER_NAME=app
ARG UID=1000
ARG GID=1000
ARG PHP_VERSION

RUN groupadd -g $GID -o $USER_NAME && \
    useradd -m -u $UID -g $GID -o -s /bin/bash $USER_NAME

RUN apt-get update && \
    apt-get install -y \
        unzip \
        nodejs \
        vim \
        npm \
        apache2 \
        openssh-client \
        software-properties-common \
        ca-certificates && \
    rm -rf /var/lib/apt/lists/*

RUN add-apt-repository ppa:ondrej/php && \
    apt-get update && \
    apt-get install -yq \
        php${PHP_VERSION} \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-pdo \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-json \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-tokenizer \
        php${PHP_VERSION}-ctype \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-xdebug \
        libapache2-mod-php${PHP_VERSION}

RUN a2dissite 000-default
RUN a2enmod rewrite

COPY resources/apache/ports.conf /etc/apache2/ports.conf
COPY resources/apache/site.conf /etc/apache2/sites-enabled/site.conf

COPY resources/php/composer.phar /usr/bin/composer


ENV APACHE_RUN_USER=$USER_NAME
ENV APACHE_RUN_GROUP=$USER_NAME
RUN chown -R $USER_NAME:$USER_NAME /var/log/apache2*
RUN chown -R $USER_NAME:$USER_NAME /var/lock/apache2*
RUN chown -R $USER_NAME:$USER_NAME /var/run/apache2*

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/apache2/access.log && \
    ln -sf /dev/stderr /var/log/apache2/error.log

USER $USER_NAME
WORKDIR /var/www/html/app

COPY resources/start.sh /usr/bin/local/start.sh
CMD ["/usr/bin/local/start.sh"]