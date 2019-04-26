# Docker file for Saito 4

# Apache Webserver with PHP 
FROM php:7.1-apache

 #install all the system dependencies and enable PHP modules 
RUN apt-get update && apt-get install -y \
      libicu-dev \
      libpq-dev \
      libmcrypt-dev \
      mysql-client \
      git \
      zlib1g-dev \
      libzip-dev \
      unzip

RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install \
      intl \
      mbstring \
      mcrypt \
      pcntl \
      pdo_mysql \
#      pdo_pgsql \
#      pgsql \
      zip \
      opcache


# Install xdebug
RUN pecl install xdebug-2.5.0
RUN docker-php-ext-enable xdebug
RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
      && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

#install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

#set our application folder as an environment variable
ENV APP_HOME /var/www/html

#change uid and gid of apache to docker user uid/gid
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

#change the web_root to cakephp /var/www/html/webroot folder
# RUN sed -i -e "s/html/html\/app\/webroot/g" /etc/apache2/sites-enabled/000-default.conf

#change the web_root to cakephp /var/www/html/webroot folder
RUN echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/memory_limit.ini

# enable apache module rewrite
RUN a2enmod rewrite

#copy source files and run composer
COPY . $APP_HOME

# install all PHP dependencies
# RUN composer install --no-interaction

#change ownership of our applications
RUN chown -R www-data:www-data $APP_HOME 
