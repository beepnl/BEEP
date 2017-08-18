#!/bin/sh 
branch=${1:-master}  
base_dir=$(pwd)

# laravel
cd $base_dir
composer install && sudo chmod -R 777 storage && sudo chmod -R 777 bootstrap/cache && php artisan migrate && php artisan vendor:publish
composer dumpautoload

# bower
cd $base_dir
cd public/webapp && bower install --allow-root