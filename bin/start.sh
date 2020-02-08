#!/bin/sh
composer install
docker-compose --file docker/docker-compose.yml up --build -d
docker exec -it worms1d-php ./bin/up.sh
docker-compose --file docker/docker-compose.yml stop
