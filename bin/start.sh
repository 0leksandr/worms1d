#!/bin/sh
docker-compose --file docker/docker-compose.yml up --build -d
docker exec -it --user _www worms1d-php ./bin/up.sh
docker-compose --file docker/docker-compose.yml stop
