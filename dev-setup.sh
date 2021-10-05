git config core.filemode false
git checkout -- .htaccess
php composer.phar install
npm ci
npm run dev
sudo chmod -R 777 plugin/storage/
