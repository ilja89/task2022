rm /opt/bitnami/php/lib/php/extensions/xdebug.so
rm /opt/bitnami/php/etc/conf.d/99-xdebug.ini
/opt/bitnami/apache/bin/apachectl -k restart
