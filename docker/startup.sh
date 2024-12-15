!/bin/sh

php artisan migrate --force

# Start the first process specific to NGINX
sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

# start fpm
php-fpm -D

while ! nc -w 1 -z 127.0.0.1 9000; do sleep 0.1; done;

nginx