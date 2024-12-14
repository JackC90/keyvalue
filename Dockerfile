FROM php:8.3-fpm-alpine

#add nginx and supervisor
RUN apk update && apk add --no-cache nginx supervisor wget

#create process for nginx
RUN mkdir -p /run/nginx

#copy configuration files to the docker image
COPY docker/nginx.conf /etc/nginx/nginx.conf

#create directory for the application
RUN mkdir -p /app

#copy whole project repo to the folder
COPY . /app

#get composer and install dependencies
RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"

RUN cd /app && \
    /usr/local/bin/composer install --no-dev --optimize-autoloader

#change owner of the folder
RUN chown -R www-data:www-data /app

#run the command for starting the server
CMD sh /app/docker/startup.sh

ENTRYPOINT ["sh", "/app/docker/laravel.sh"]