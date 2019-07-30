#!/bin/sh 

# on AWS

# put back latest mysql backup
echo "Importing MySQL..."
MYSQL_PASS=$(grep -oP '^DB_PASSWORD=\K.*' /home/bitnami/apps/BEEP/.env)
zcat /home/bitnami/backups/mysql/bee_base.sql.gz | mysql -u beep -p"$MYSQL_PASS" bee_base

#cd /home/bitnami/apps/BEEP/
# php artisan migrate
# php artisan db:seed

# put back latest influx backup
echo "Importing InfluxDB..."
influxd restore -database bee_data -metadir /var/lib/influxdb/meta -datadir /var/lib/influxdb/data /home/bitnami/backups/influx

echo "Finished importing"

exit 0