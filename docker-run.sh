#!/bin/sh

set -x
set -e

pwd

# Setup Storage Directory structure
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
cp storage.bak/app/new_taxonomy_tables.sql storage/app
chmod 777 -R storage


# Migrate DB
php artisan migrate

# Link storage
php artisan storage:link

# Set API_URL
sed -i '/^var API_URL/d' public/app/js/constants.js
echo "" >> public/app/js/constants.js
echo "var API_URL = '${API_URL}';" >> public/app/js/constants.js

cat public/app/js/constants.js

# Start Service
apache2-foreground