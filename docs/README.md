# Development moodle installation guide


Intention of the following document is to make it easier to get up and going with developing Charon in localhost. By default, it's complicated to get Charon to work with fresh Moodle. By following this tutorial, you're 15 minutes apart from perfectly working 1:1 development environment which is running at ained.ttu.ee.

## Disclaimer
Keep in mind that this tutorial is written by taking MacOS Mojave environment into account. While majority of the commands remain same for Ubuntu for example, you may still need to double-check your environment for possible mismatches.

## Prerequisites

Not much. Just **Docker CE** and **Git**. I'm not going to cover the installation of Docker since it's pretty platform-specific, but it shouldn't be much of a hassle by following [original docker install tutorial.](https://docs.docker.com/install/)



## Creating directories

Let's create a structured folder somewhere in your home directory, for example:

``` bash
$ mkdir -p ~/dev/moodle
$ mkdir -p ~/dev/moodle/moodle_data
$ mkdir -p ~/dev/moodle/mariadb_data
```
```moodle_data``` and ```mariadb_data``` folders are required in order to have persistent storage of Moodle and it's database- otherwise all changes to plugin will be lost. ```~/dev/moodle``` itself is a location free of choice.

## Firing up containers
Whole setup is done using docker containers. We need two containers- one for our webserver with Moodle and another one for database. Docker-compose makes it possible to boot them at the same time.

In the docs/ folder, please find **docker-compose.yml** and download it to your moodle conf dir, in our case ```~/dev/moodle/```.

When done, simply run:
```bash
$ cd ~/dev/moodle/
$ docker-compose up -d
```
Containers should be up and running now. When initially booted, it takes time until moodle gets installed. Let's take a look how it's going.

Use command ```docker ps``` to get information about running containers. Capture container ID for image ```bitnami/moodle```. Run ```docker logs <containerID> -f``` to follow logs. It should expose how httpd is installed and moodle configured.

When done, you should be able to access Moodle at http://localhost.

Default user is ```dev``` and password ```dev``` as well.

## Best way to install charon

Either run `docker-compose up -d` or `docker-compose -f docker-compose-cloud.yml up -d` and `docker-compose -f docker-compose-moodle-use-cloud.yml up -d`

Then run `chmod -R 777 mariadb_data` and run the initial docker-compose commands again

To install charon you can either use 
```
curl -v -X GET --header "PRIVATE-TOKEN: [token]" -o artifacts.zip "https://gitlab.cs.ttu.ee/api/v4/projects/216/jobs/artifacts/master/download?job=create_production_artifacts"
```
or 
```
git clone https://gitlab.cs.ttu.ee/ained/charon
```

When git clone option is used you need to `docker exec -it <hash> bash` into the container, `cd bitnami/moodle/mod/charon` folder and follow this tutorial on how to install npm: https://linuxize.com/post/how-to-install-node-js-on-ubuntu-18.04/
and then run next commands as well: 
```
php composer.phar install --no-dev
cp -p .env.production .env
php artisan key:generate
npm shrinkwrap
npm install
npm run dev
rm -rf node_modules
```

## Post installation

Then `cd moodle_data/moodle/mod/charon` and run 
```
sudo chmod -R 777 plugin/storage/
```

Now you should have a working charon

If you notice, that when creating charons the presets and defaults are not present - then installation goofed and command:
`php artisan db:seed` should be ran which seeds the database.

## Installing TTU theme

It may help to develop views for Charon when running the same theme which is used at ained.ttu.ee. 

Go to your Moodle installation themes folder by
 ```cd ~/dev/moodle/moodle_data/moodle/theme```

And download TTU theme with 
```git clone https://gitlab.cs.ttu.ee/ained/theme1.git```

Afterwards, navigate to your Moodle instance from browser. You should be prompted with new plugin installation page.

Make TTU theme default by navigating to **Site administration > Appearance > Theme > Theme selector > Default > Change theme**.

Select **Theme1** from the list.

## Why .htacces is used
.htaccess is needed so `/mod/charon/courses/<id>/settings` doesn't return 404. Purely because how Moodle is done