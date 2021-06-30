#!/bin/sh 

SOURCE="/home/bitnami"
TARGET="/home/temp"

sudo mkdir $TARGET

cd $SOURCE

echo "Copying file by file from $SOURCE to $TARGET..."

find . -type d -exec mkdir -p "$TARGET{}" \;
find . -type f -exec dd if={} of="$TARGET{}" bs=8M oflag=direct \;

echo "Finished copying $SOURCE to $TARGET"
