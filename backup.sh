 #!/bin/sh 

DATE=$(date +"%Y-%m-%d")

cd ~/
if [ ! -d "backups" ]
then
	sudo mkdir backups
fi

if [ ! -d "backups/mysql" ]
then
	sudo mkdir backups/mysql
fi

if [ ! -d "backups/influx" ]
then
	sudo mkdir backups/influx
fi

echo "Backing up MySQL database"
MDIR="backups/mysql/$DATE"
MYSQL_PASS=$(grep -oP '^DB_PASSWORD=\K.*' apps/BEEP/.env)
 /opt/bitnami/mysql/bin/mysqldump -u beep -p"$MYSQL_PASS" bee_base | gzip > backups/mysql/bee_base.sql.gz
if [ -d "$MDIR" ]
then
	sudo rm -rf $MDIR
fi
mkdir $MDIR
sudo cp backups/mysql/bee_base.sql.gz $MDIR

echo "Backing up Influx database"
IDIR="backups/influx/$DATE"

if [ -d "$IDIR" ]
then
	sudo rm -rf $IDIR
fi
mkdir $IDIR
influxd backup -database bee_data -retention autogen $IDIR

sudo rm backups/influx/*.00
sudo cp $IDIR/*.00 backups/influx/

exit 0