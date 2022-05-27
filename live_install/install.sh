#!/usr/bin/env bash

# Run this file in Moodle server as root 


MOODLE=/var/www/html
CHARON=$MOODLE/mod/charon
USER=www-data:www-data
YES=y # depends on moodle default language
      # use Yes=jah for Estonian


die () {
    echo >&2 "$@"
    exit 1
}


if [ "$#" = 0 ]; then
    printf "Arguments: <charon file> "
    die
fi


SOURCE=$1

if [ ! -f "$SOURCE" ]; then
    printf "Charon file not found"
    die
fi


if [ -d "charon" ]; then
    rm -r "charon"
fi


unzip "$SOURCE"
chmod -R 0755 charon/
find charon/ -type f -exec chmod 0644 {} \;
chown -R $USER charon/
cp -rpT charon  $CHARON
chmod -R 775 $CHARON/plugin/storage
find $CHARON/plugin/storage -type f -exec chmod 0664 {} \;
#sh clear_opcache.sh $ENV
cd $MOODLE

echo $YES | php admin/cli/upgrade.php # answer "y" to prompt
php admin/cli/purge_caches.php
