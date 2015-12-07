FROM ubuntu:latest

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

RUN apt-get update -qq && \
    apt-get install -y \
    php5 \
    php5-cli \
    php5-intl \
    curl \
    zip \
    unzip \
    git

ADD files/etc/crontab /etc/crontab
ADD files/bin/start-cron.sh /usr/bin/start-cron.sh
RUN chmod +x /usr/bin/start-cron.sh
RUN touch /var/log/cron.log

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

# Move application files into place
COPY src/ /home/app/

RUN chmod +x /home/app/run.sh

# remove any development cruft
RUN rm -rf /home/app/vendor/*

WORKDIR /home/app

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

USER root

RUN /usr/bin/start-cron.sh
