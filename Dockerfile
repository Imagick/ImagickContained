FROM debian:9

USER root

# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    wget curl redis-server \
    ca-certificates lsb-release apt-transport-https gnupg bsdmainutils

# Install 3rd party PHP 7.2 packages
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list \
    && curl https://packages.sury.org/php/apt.gpg | apt-key add - \
    && apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y php7.2 php7.2-common php7.2-cli \
    php7.2-mbstring \
    php7.2-intl php7.2-redis \
    php7.2-imagick supervisor

# Make the default directory you
WORKDIR /var/app

RUN mkdir -p /var/log/supervisor

# Create some directories for redis to hold its data in
RUN mkdir /data && chown redis:redis /data
VOLUME /data
# WORKDIR /data


COPY docker/redis.conf /etc/redis.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/tasks/image_processor.conf /etc/supervisor/conf.d/image_processor.conf
COPY docker/tasks/redis.conf /etc/supervisor/conf.d/redis.conf

COPY process_images.php /var/app/process_images.php
#COPY composer.json /var/app/composer.json
#COPY composer.lock /var/app/composer.lock

COPY libImagickContained/ /var/app/libImagickContained/
# COPY auryn/ /var/app/auryn/
# COPY vendor/ /var/app/vendor/


# CMD tail -f /etc/supervisor/supervisord.conf
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
