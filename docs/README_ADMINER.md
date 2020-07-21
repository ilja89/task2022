# Log in database with Adminer guide

Intention of the following document is to make it easier to start working with database using Adminer.

## Using local database

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























