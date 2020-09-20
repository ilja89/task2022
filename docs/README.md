# Development moodle installation guide

Intention of the following document is to make it easier to get up and going with developing Charon in localhost. By default, it's complicated to get Charon to work with fresh Moodle. By following this tutorial, you're 15 minutes apart from perfectly working 1:1 development environment which is running at ained.ttu.ee.

## Installing moodle

Either run `docker-compose up -d` (has mount for charon) or `docker-compose -f docker-compose-cloud.yml up -d` and `docker-compose -f docker-compose-moodle-use-cloud.yml up -d`

When done, you should be able to access Moodle at http://localhost and login with user ```dev```, password ```dev```, configurable in docker-compose.

## Best way to install charon

Before proceeding with charon install to moodle, make sure your docker has enough resources for npm install. 

To install charon you can either use 
```
curl -v -X GET --header "PRIVATE-TOKEN: [token]" -o artifacts.zip "https://gitlab.cs.ttu.ee/api/v4/projects/216/jobs/artifacts/master/download?job=create_production_artifacts"
```
or 
```
git clone https://gitlab.cs.ttu.ee/ained/charon
```

When git clone option is used you need to `docker exec -it <hash or name> bash` into the container, `cd bitnami/moodle/mod/charon` folder and follow this tutorial on how to install npm: https://linuxize.com/post/how-to-install-node-js-on-ubuntu-18.04/
and then run next commands as well: 
```
cp -p .env.production .env
php composer.phar install --no-dev
apt install -y build-essential libpng-dev
npm install
npm run dev
rm -rf node_modules
```

## Post installation

Then `cd moodle_data/moodle/mod/charon` and run 
```
sudo chmod -R 777 plugin/storage/
```

Now you should have a working charon. Also enable developer settings by going **Site administration > Development > Debugging** and setting **Debug messages** to ```DEVELOPER```.

If you notice that when creating charons the presets and defaults are not present - then installation goofed and command:
`php artisan db:seed` should be ran which seeds the database.

## Installing TTU theme

It may help to develop views for Charon when running the same theme which is used at ained.ttu.ee, ```git clone https://gitlab.cs.ttu.ee/ained/theme1.git``` 

There are 2 options:
    * You can download the folder and copy it from host machine into the container: ```docker cp <theme_folder> <container hash/name>:bitnami/moodle/theme/<theme_folder>```
    * In case of moodle being mounted to a folder, you can just drop or clone the folder into the ```theme``` folder on your host machine.

Afterwards, navigate to your Moodle instance from browser. You should be prompted with new plugin installation page.

Make TTU theme default by navigating to **Site administration > Appearance > Theme > Theme selector > Default > Change theme**.

Select **Theme1** from the list.


## Using Adminer

Docker-composes also have an Adminer container for easy access to database. To use it, navigate to localhost or server IP, to the port where Adminer container is (check from docker-compose or in case of cloud install, ask whoever knows about that). 

Server field is in context of docker, for local install it would be ```mariadb:3306```, user and pass both ```root```, database ```bitnami_moodle```.

## Why .htacces is used
.htaccess is needed so `/mod/charon/courses/<id>/settings` doesn't return 404. Purely because how Moodle is done