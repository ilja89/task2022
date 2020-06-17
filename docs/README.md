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

## Downloading Charon

When confirmed that moodle is running well on http://localhost, it's time to move on with Charon installation. 

Remember we used to make ```~/dev/moodle/``` directory before? Since 1:1 of container content is mounted at that directory, it's where we can install charon, too.

Go to moodle mod folder by
```$ cd ~/dev/moodle/moodle_data/moodle/mod``` and clone charon repo with
 ```$ git clone https://gitlab.cs.ttu.ee/ained/charon.git```. You may be prompted for authorization credentials of gitlab credentials.

## Configuring  & Activating Charon
When Charon is downloaded, it's time to configure it.

Since we're using persistent storage and file paths are different when it comes to container, we have to launch commands below in container itself.

Obtain Moodle container ID from `docker ps` and run
`docker exec -ti <CONTAINER_ID> bash`. 

Now we're in container's terminal. In that same terminal, navgate to Charon plugin folder by  `$ cd bitnami/moodle/mod/charon` and run the following commands to make Laravel (php framework of charon) work:
 
  ```
 php composer.phar install &&
 cp -p .env.example .env &&
php artisan key:generate
```
 
Charon views are being developed using Vue. Install npm packages with  `npm i`. When done, close container terminal.
 
 We're done with configuration now. Go to http://localhost and log in with `dev` as user and `dev` as password. You should be welcomed with new plugin install window. Install and Proceed.

Verify that Laravel routing of Charon works with navigating to http://localhost/mod/charon/documentation.

Moodle is now installed along with Charon.


## Installing TTU theme

It may help to develop views for Charon when running the same theme which is used at ained.ttu.ee. 

Go to your Moodle installation themes folder by
 ```$ cd ~/dev/moodle/moodle_data/moodle/theme```

And download TTU theme with 
```git clone https://gitlab.cs.ttu.ee/ained/theme1.git```

Afterwards, navigate to your Moodle instance from browser. You should be prompted with new plugin installation page.

Make TTU theme default by navigating to **Site administration > Appearance > Theme > Theme selector > Default > Change theme**.

Select **Theme1** from the list.


## Known issues

Linked volumes in Docker CE using macOS could be painfully slow. You might want to try bleeding edge versions of Docker CE when the problem affects you.
