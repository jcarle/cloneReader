#!/bin/bash

DIR=$(dirname "$(readlink -f "$0")")


docker stop cloneReader-mysql;
docker rm cloneReader-mysql;

docker run --name cloneReader-mysql -p 3306:3306 -e MYSQL_ROOT_PASSWORD=root \
	-e TZ=America/Buenos_Aires \
	-v "$DIR/../.docker/dump:/tmp/backup" \
	-v "$DIR/../.docker/var/lib/mysql:/var/lib/mysql" \
	-v "$DIR/../.docker/etc/mysql/my.cnf:/etc/mysql/my.cnf" \
	-d mysql:5.7.21 --sql-mode="NO_ENGINE_SUBSTITUTION";


# to dump database:
# docker exec  -u 0 -i -t  cloneReader-mysql bash
# mysql -p
# CREATE DATABASE cloneReader CHARACTER SET utf8 COLLATE utf8_general_ci;
# CTR+C
# mysql -p cloneReader < /tmp/backup/cloneReader.20190519.sql
