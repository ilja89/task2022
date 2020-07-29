# Log in database with Adminer guide

Intention of the following document is to make it easier to start working with database using Adminer.

## Using local database

Needed file is **docker-compose.yml**

If you want use local database, you can meet such trouble like your db container will not able to start again after stopping. Right now we found temporary solution, let`s talk about it.

#### Change password for root

By default our database have user 'root' without password, but Adminer don`t support entering user without password, so wee need to change password before trying to log in db with Adminer.

Firstly, you need to open the console. After that check your containers list with next command

```Bash
$ docker ps -a
```

You need container with image like '**bitnami/mariadb:latest**', be sure that container is running.

Next step: getting an interactive bash session into container

```Bash
$ docker exec -ti <container id> bash
```

Log in to MySQL as a root user without a password. And change/set password for root.

```Bash
$ mysql -u root
> SET PASSWORD FOR 'root' = PASSWORD('new password');
```

#### Log in with Adminer

Now you can open localhost with your port of Adminer(port you can check in the docker-compose.yml file). In our example port is 8190 and link is http://localhost:8190/

Login settings:
```
System: MySQL
Server: <Your container name, check with 'docker ps -a', smth like 'moodle_mariadb_1'>
Username: root
Password: <your password>
Database: bitnami_moodle
```

In result, you can see and work with your database. But if you stop container, this container will not able to run again. It is not cool, let`s watch temporary solution.

#### Temporary solution

Solution is to change root password, for making root user without password again. You need to repeat the previous instructions with one difference in this command 

```Bash
$ mysql -u root
> SET PASSWORD FOR 'root' = PASSWORD('');
```

After that you can stop your container without fears.

## Using database in the cloud

Needed file is **docker-compose-moodle-cloud.yml** (Rename this file to **docker-compose.yml** before starting 'docker-compose up' command. )

For using this method you need to have some database in the cloud. In our case we have running database instances at 35.217.3.154:adminer port | maria_db port

- 8280|3326 - moodle_devops2_mariadb_1

- 8380|3338 - moodle_devops3_mariadb_1

- 8480|3346 - moodle_devops4_mariadb_1

#### Choose ports for working with database

For example we will use 
**8280|3326 - moodle_devops2_mariadb_1**

- 8280 - Adminer port
- 3326 - Maria db port
- moodle_devops2_mariadb_1 - Server

#### Change port number in the docker-compose file

Before you run "docker-compose up" command you need to change port in the **docker-compose.yml** file.

You need to change MARIADB_PORT_NUMBER=3306 line, in our case we change the number to 3326

After that you can run 'docker-compose up' and install Moodle and Charon according to the usual instructions.

#### Log in with Adminer

You can open the Adminer page with your Adminer port http://35.217.3.154:8280/

For log in use parameter **server** from step **'Choose ports for working with database'**

Login settings:
```
System: MySQL
Server: moodle_devops2_mariadb_1
Username: root
Password: root
Database: bitnami_moodle
```

If username or password is incorrect, contact with person who created this database in the cloud.










