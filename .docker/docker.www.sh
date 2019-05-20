#!/bin/bash

DIR=$(dirname "$(readlink -f "$0")")

docker stop clonereader-www
docker rm clonereader-www

docker run -d -ti \
  --name clonereader-www \
  -p 80:80 \
  -e TZ=America/Buenos_Aires \
  -v "$DIR/..:/var/www/cloneReader" \
  -v "$DIR/apc/:/var/www/apc.cloneReader"   \
  -v "$DIR/etc/hosts:/etc/hosts"  \
  -v "$DIR/etc/nginx/ssl:/etc/nginx/ssl"  \
  -v "$DIR/etc/nginx/nginx.conf:/etc/nginx/nginx.conf"  \
  -v "$DIR/etc/nginx/.htpasswd:/etc/nginx/.htpasswd"  \
  -v "$DIR/etc/nginx/fastcgi_params:/etc/nginx/fastcgi_params"  \
  -v "$DIR/etc/nginx/sites-enabled:/etc/nginx/sites-enabled/" \
  -v "$DIR/etc/php/7.0/fpm/php.ini:/etc/php/7.0/fpm/php.ini" \
  -v "$DIR/etc/php/7.0/fpm/conf.d/20-apcu.ini:/etc/php/7.0/fpm/conf.d/20-apcu.ini" \
  -v "$DIR/etc/php/7.0/fpm/pool.d/www.conf:/etc/php/7.0/fpm/pool.d/www.conf" \
  -v "$DIR/etc/phpmyadmin/config.inc.php:/var/www/phpmyadmin/config.inc.php" \
  clonereader-www


docker exec  -u 0 -i -t  clonereader-www /etc/init.d/nginx restart;
docker exec  -u 0 -i -t  clonereader-www /etc/init.d/php7.0-fpm restart;
