#!/bin/sh 
branch=${1:-master}  

# git
sudo git checkout $branch
sudo git fetch --all
sudo git reset --hard origin/$branch

php artisan down

./run_actions.sh $branch

php artisan up
