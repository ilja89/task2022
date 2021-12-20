touch .env
php composer.phar install --no-dev
cp -p env.production .env
php artisan key:generate
echo "window.appVersion.date = $(git log -1 --format=%ct)000;" >> ${BOOTSTRAP_JS}
echo "window.appVersion.commit = '$(git rev-parse --verify HEAD)';" >> ${BOOTSTRAP_JS}
echo "window.appVersion.branch = '$1';" >> ${BOOTSTRAP_JS}
npm ci
npm run production

#clean up artifacts
rm -rf node_modules
rm -rf .git*
rm -rf tester_callbacks
#rm -rf plugin/resources/assets/js 		# js sources can be removed, too
rm composer.*
rm env.*
if [[ $1 == "master" || $1 == "release"* ]]; then
	rm -rf coverage
fi
rm -rf xdebug
#add more to remove

mkdir charon
mv * charon || true
mv .* charon || true
