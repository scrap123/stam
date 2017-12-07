#!/usr/bin/env bash

rm -r -f vendor
rm -r -f Model/Base
rm -r -f Model/Mapping
mkdir vendor
cp -r gen/vendor/restler.php vendor/
cp -r gen/vendor/Extension vendor/
composer install
rm -r -f vendor/mandango
cp -r gen/vendor/mandango vendor
php Mondator.php
docker-compose down
docker-compose up
