#!/usr/bin/env bash

# Run this file in Moodle server as root 


MOODLE=/srv/moodle/public_html
CHARON=$MOODLE/mod/charon
USER=moodle:http


die () {
    echo >&2 "$@"
    exit 1
}


if [ "$#" = 0 ]; then
    printf "Arguments: [-u] <charon file> "
    die
fi


UPDATE=0

while getopts ":u" opt; do
    case ${opt} in
        u ) 
            UPDATE=1
            shift
            ;;
        \? ) echo "Unknown option"
            die
            ;;
    esac
done


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

echo "y" | php admin/cli/upgrade.php # answer "y" to prompt
php admin/cli/purge_caches.php
cd mod/charon


if [ $UPDATE = 1 ]; then

    php artisan optimize:clear

else

    php artisan key:generate --force
    php artisan db:seed --force

fi