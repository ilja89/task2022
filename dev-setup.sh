git config core.filemode false || :
git checkout -- .htaccess || :
cp composer.phar /opt/bitnami/php/bin
composer install
npm ci
npm run dev
sudo chmod -R 777 plugin/storage/
