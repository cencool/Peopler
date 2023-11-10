#!/bin/bash

echo "Deploying web to server"
echo "Get ready!"
if test -d /var/www/peopler
then
    echo "folder peopler exists"
else
    echo "folder peopler does not exist!"
fi

rsync -a --exclude uploads --exclude deploy_web.sh --exclude .git --exclude config/db.php ./ /var/www/peopler
chown -R root:root /var/www/peopler

if [ ! -d /var/www/peopler/uploads ]
then
mkdir /var/www/peopler/uploads
fi

if [ ! -d /var/www/peopler/uploads/delete ]
then
mkdir /var/www/peopler/uploads/delete
fi

if [ ! -d /var/www/peopler/uploads/person_photo ]
then 
mkdir /var/www/peopler/uploads/person_photo
fi

if [ ! -d  /var/www/peopler/uploads/thumbnails ]
then
mkdir /var/www/peopler/uploads/thumbnails
fi

chown -R www-data:www-data /var/www/peopler/uploads
chown -R www-data:www-data /var/www/peopler/runtime
chown -R www-data:www-data /var/www/peopler/web/assets

