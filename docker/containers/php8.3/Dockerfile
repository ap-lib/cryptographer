FROM ubuntu:24.04
RUN apt-get update

#--- tools ---
RUN apt-get install \
    curl \
    host \
    mc \
    nmap \
    wget \
    net-tools -y

#--- php ---
RUN apt-get install software-properties-common -y
RUN LC_ALL=C.UTF-8 add-apt-repository -yu ppa:ondrej/php
RUN apt-get update
RUN apt-get install php-pear -y
RUN apt-get install php8.3 -y
RUN apt-get install php8.3-cli -y
RUN apt-get install php8.3-common -y
RUN apt-get install php8.3-dev -y

RUN update-alternatives --set php /usr/bin/php8.3

#--- composer ---
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

#--- PHPUnit ---
RUN wget -O phpunit https://phar.phpunit.de/phpunit.phar
RUN chmod +x phpunit
RUN mv phpunit /usr/local/bin/phpunit

#--- clean ---
RUN rm -rf /tmp/*
RUN apt-get clean

ENTRYPOINT tail -f /dev/null
