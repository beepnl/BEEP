#!/bin/sh 
branch=${1:-master}  

# git
git checkout $branch
git fetch --all
git reset --hard origin/$branch

php artisan down

./run_actions.sh $branch

php artisan up
