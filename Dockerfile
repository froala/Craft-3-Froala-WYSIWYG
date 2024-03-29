FROM php:8.1.19-apache
WORKDIR /var/www/html/

ENV APACHE_DOCUMENT_ROOT /var/www/html/
ENV CRAFT_ALLOW_SUPERUSER 1
ARG PackageName
ARG PackageVersion
ARG NexusUser
ARG NexusPassword

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y git zip unzip zlib1g-dev libzip-dev wget netstat-nat net-tools libmagickwand-dev libicu-dev zlib1g-dev libicu-dev g++ --no-install-recommends \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

#RUN apt-get update && apt-get install -y libmagickwand-dev libicu-dev zlib1g-dev libicu-dev g++ --no-install-recommends && rm -rf /var/lib/apt/lists/*
RUN pecl install imagick-beta
#RUN docker-php-ext-enable imagick

RUN docker-php-ext-install zip \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install bcmath \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && docker-php-ext-enable imagick


WORKDIR /var/www/html/
RUN composer create-project craftcms/craft=^1 .
# COPY . .
RUN chmod -R 777 /var/www/html/
# RUN composer global config --no-plugins allow-plugins.craftcms/plugin-installer true
RUN cat composer.json

RUN chmod -R 777 /var/www/html/config
RUN chmod -R 777 /var/www/html/web/cpresources
RUN chmod -R 777 /var/www/html/composer.json
# RUN chmod -R 777 /var/www/html/craft/

# RUN composer config --no-plugins allow-plugins.composer/installers true

RUN composer require froala/craft-froala-wysiwyg

RUN cat composer.json

RUN composer install

COPY . /var/www/html/vendor/froala/craft-froala-wysiwyg

RUN wget --no-check-certificate --user ${NexusUser}  --password ${NexusPassword} https://nexus.tools.froala-infra.com/repository/Froala-npm/${PackageName}/-/${PackageName}-${PackageVersion}.tgz
RUN tar -xvf ${PackageName}-${PackageVersion}.tgz

RUN cp -a package/. /var/www/html/vendor/froala/wysiwyg-editor/
RUN rm -rf package ${PackageName}-${PackageVersion}.tgz

#RUN ./craft plugin/install froala-editor

EXPOSE 80
RUN sed -ri -e "s|/var/www/html|${APACHE_DOCUMENT_ROOT}|g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s|/var/www/|${APACHE_DOCUMENT_ROOT}|g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN chown -R www-data:www-data /var/www/html/
RUN chown -R www-data:www-data /var/www/html/craft
RUN a2enmod rewrite