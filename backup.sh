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
MDIR="/home/bitnami/backups/mysql/$DATE"
MYSQL_PASS=$(/bin/grep -oP '^DB_PASSWORD=\K.*' /home/bitnami/apps/BEEP/.env)
/opt/bitnami/mysql/bin/mysqldump -u beep -p"$MYSQL_PASS" bee_base | /bin/gzip > /home/bitnami/backups/mysql/bee_base.sql.gz

if [ -d "$MDIR" ]
then
	echo "Removing existing MySQL backup dir $MDIR"
	sudo rm -rf $MDIR
fi
sudo mkdir $MDIR
echo "Copying MySQL backup to dir $MDIR"
sudo cp /home/bitnami/backups/mysql/bee_base.sql.gz $MDIR

echo "Backing up Influx database"
IDIR="/home/bitnami/backups/influx/$DATE"

if [ -d "$IDIR" ]
then
	echo "Removing existing Influx backup dir $IDIR"
	sudo rm -rf $IDIR
fi
sudo mkdir $IDIR
influxd backup -database bee_data -retention autogen $IDIR

sudo rm /home/bitnami/backups/influx/*.00
echo "Copying Influx backup to dir $IDIR"
sudo cp $IDIR/*.00 /home/bitnami/backups/influx/

echo "Finished backup for $DATE"
echo ""

exit 0
