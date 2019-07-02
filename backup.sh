 #!/bin/sh 

cd ~/
sudo mkdir backups
sudo mkdir backups/mysql
sudo mkdir backups/influx

echo "Backing up MySQL database"
sudo mv backups/mysql/bee_base.sql.gz-1 backups/mysql/bee_base.sql.gz-2
sudo mv backups/mysql/bee_base.sql.gz backups/mysql/bee_base.sql.gz-1
mysqldump -u beep -p'qLHYrFhkaYEM9r7L' bee_base | gzip > backups/mysql/bee_base.sql.gz

echo "Backing up Influx database"
DATE=$(date +"%Y-%m-%d")
IDIR="backups/influx/$DATE"
sudo mkdir $IDIR
influxd backup -database bee_data -retention autogen $IDIR

exit 0