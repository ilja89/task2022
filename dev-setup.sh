git config core.filemode false
git checkout -- .htaccess
php composer.phar install
npm config set registry https://registry.npmjs.org/
npm ci
npm run dev
sudo chmod -R 777 plugin/storage/
