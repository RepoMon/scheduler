FROM php:latest

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

EXPOSE 80

RUN apt-get update -qq && \
    apt-get install -y \
    libicu-dev \
    cron

# install bcmath and mbstring for videlalvaro/php-amqplib
RUN docker-php-ext-install bcmath mbstring pdo_mysql

ADD files/etc/crontab /etc/cron.d/schedule
RUN touch /var/log/cron.log

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run-all.sh"]

# Move application files into place
COPY src/ /home/app/

RUN chmod +x /home/app/run.sh
RUN chmod +x /home/app/run-all.sh

WORKDIR /home/app

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

USER root
