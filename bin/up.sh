#!/bin/sh
composer install
"$(dirname "$0")"/symfony.sh self:update -y
"$(dirname "$0")"/symfony.sh serve --port=6150
