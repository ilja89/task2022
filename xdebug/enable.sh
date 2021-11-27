cp /bitnami/moodle/mod/charon/xdebug/xdebug.so /opt/bitnami/php/lib/php/extensions
cp /bitnami/moodle/mod/charon/xdebug/99-xdebug.ini /opt/bitnami/php/etc/conf.d
export set XDEBUG_SESSION=PHPSTORM
/opt/bitnami/apache/bin/apachectl -k restart
