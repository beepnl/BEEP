#!/bin/sh 

# on AWS
# put back mysql
echo "Importing MySQL..."
sudo mysql -u beep -p bee_base < /home/bitnami/backups/mysql/bee_base.sql
cd /home/bitnami/apps/BEEP/
php artisan migrate
php artisan db:seed

#import influx
echo "Importing InfluxDB..."
sudo influxd restore -database bee_data -metadir /var/lib/influxdb/meta -datadir /var/lib/influxdb/data /home/bitnami/backups/influx

echo "Finished importing"

exit 0