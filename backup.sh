#!/bin/sh

DATE=$(date +"%Y-%m-%d")

if [ ! -d "/home/bitnami/backups" ]
then
	sudo mkdir /home/bitnami/backups
fi

if [ ! -d "/home/bitnami/backups/mysql" ]
then
	sudo mkdir /home/bitnami/backups/mysql
fi

if [ ! -d "/home/bitnami/backups/influx" ]
then
	sudo mkdir /home/bitnami/backups/influx
fi

echo "Backing up MySQL database"
MYSQL_USER=$(/bin/grep -oP '^DB_USERNAME=\K.*' /home/bitnami/apps/BEEP/.env)
MYSQL_PASS=$(/bin/grep -oP '^DB_PASSWORD=\K.*' /home/bitnami/apps/BEEP/.env)
/opt/bitnami/mysql/bin/mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASS" bee_base | /bin/gzip > /home/bitnami/backups/mysql/bee_base-$DATE.gz

echo "Remove MySQL backups older than 14 days"
sudo find /home/bitnami/backups/mysql/* -mtime +14 -delete

# Sync todays backup to S3 (delete all available files there, and only sync the files inside the root folder + all folders that are named like the first of the month)
echo "Upload MySQL bee_base-$DATE.gz to s3"
/usr/bin/aws s3 cp "/home/bitnami/backups/mysql/bee_base-$DATE.gz" "s3://beep-database-backups/mysql/bee_base-$DATE.gz"

echo "Backing up Influx database"
influxd backup -database bee_data -retention autogen /home/bitnami/backups/influx
sudo rm /home/bitnami/backups/influx/*.pending

echo "Compress Influx backup to file and remove backups older than 14 days"
/bin/tar -czf "/home/bitnami/backups/influx/bee_data-$DATE-influx.gz" /home/bitnami/backups/influx/*.00 && sudo rm /home/bitnami/backups/influx/*.00 && sudo find /home/bitnami/backups/influx/* -mtime +14 -delete

# Sync todays backup to S3 (option: delete all available files there, and only sync the files inside the home/bitnami folder + all folders that are named like the first of the month: --exclude "*/*" --include "????-??-01/*")
echo "Upload Influx bee_data-$DATE-influx.gz to s3"
/usr/bin/aws s3 cp "/home/bitnami/backups/influx/bee_data-$DATE-influx.gz" "s3://beep-database-backups/influx/bee_data-$DATE-influx.gz"

echo "Finished backup for $DATE"
echo ""

exit 0
