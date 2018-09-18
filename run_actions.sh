#!/bin/sh 
branch=${1:-master}  
base_dir=$(pwd)

# laravel
cd $base_dir
if [ ! -f '.env' ]; then cp .env.example .env && php artisan key:generate; fi
composer install && sudo chmod -R 777 storage && sudo chmod -R 777 bootstrap/cache && php artisan migrate && php artisan vendor:publish && php artisan storage:link
composer dumpautoload

# bower
cd $base_dir
cd public/webapp && bower install --allow-root

