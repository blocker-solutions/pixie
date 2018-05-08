# BASE.
FROM ambientum/php:7.2-nginx
# ARG
ARG APP_VERSION=local
# ENV
ENV APP_VERSION ${APP_VERSION}

# FILES.
ADD . /var/www/app
# PREPARE.
RUN cd /var/www/app && \
    echo -e "\n---> [1/3] Fixing permissions \n" && \
    sudo chown -R ambientum:ambientum /var/www/app && \
    sudo chown -R ambientum:ambientum /home/ambientum && \
    echo -e "\n ---> [2/3] Installing Dependencies \n" && \
    composer install --no-interaction --no-progress --prefer-dist && \
    echo -e "\n ---> [3/3] Cleaning Up \n" && \
    rm -rf /home/ambientum/.composer

ADD .opcache.ini /etc/php7/conf.d/00_opcache.ini
ADD .nginx-host.conf /etc/nginx/sites/laravel.conf
ADD .nginx.conf /etc/nginx/nginx.conf