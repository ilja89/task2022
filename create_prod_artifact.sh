touch .env
php composer.phar install --no-dev
cp -p env.production .env
php artisan key:generate
echo "window.appVersion.date = $(git log -1 --format=%ct)000;" >> ${BOOTSTRAP_JS}
echo "window.appVersion.commit = '$(git rev-parse --verify HEAD)';" >> ${BOOTSTRAP_JS}
echo "window.appVersion.branch = '$1';" >> ${BOOTSTRAP_JS}
npm shrinkwrap
npm install
npm run production
rm -rf node_modules
mkdir charon
mv * charon || true
mv .* charon || true
