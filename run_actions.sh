#!/bin/sh 
branch=${1:-master}  
base_dir=$(pwd)

# backup data before update
read -p "Backup MySQL and Influx database (y/N)? " backup_db

if [ "$backup_db" = "y" ]; then
	echo "Backing up the databases..."
	./backup.sh
fi

# laravel
cd $base_dir
if [ ! -f '.env' ]; then cp .env.example .env && php artisan key:generate; fi
composer install && sudo chmod -R 777 storage && sudo chmod -R 777 bootstrap/cache && php artisan migrate  && php artisan storage:link

#npm run dev
