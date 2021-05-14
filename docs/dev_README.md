# Charon install readme for Charon devs
This is a WIP document, where I document the steps I take while setting up Charon dev environment.  


## 0. File permissions
`Working directory: ~/WebstormProjects/charon`  
  
By default, git picks up changes in file permissions(https://linuxhandbook.com/linux-file-permissions/).      
During the development process, Moodle/Charon might change the file permissions on your machine.  
Run `git config core.filemode false` to disable tracking of filemode changes for this project.


## 1. Moodle install
`Working directory: ~/WebstormProjects/charon/docs`

### docker-compose.yml
`docker-compose up -d`  
Builds and starts containers described in `docker-compose.yml`.
Containers are moodle, moodle database and adminer.  
Moodle container uses volumes to synchronize your project folder with moodle folder inside the container.  
The first build will take some time. You can see the progress by running `docker logs -f charon-moodle`.  
After it says `moodle successfully installed` you can *safely* proceed with installation.  

### database upgrades and things
After the previous step you should be able to access your local Moodle instance.  
Go to `localhost` and log in using (`dev`, `dev`).  
You will be asked some stuff, just agree to things without changing anything.  

### debug mode
You can actually get much more logs from your moodle than now. Go to **Site administration > Development > Debugging**  
and set **Debug messages** to `DEVELOPER`

## 2. Charon install 
`Working directory: ~/WebstormProjects/charon/`

### connect to docker container
Run `docker exec -ti charon-moodle bash`  
Go to charon folder `cd bitnami/moodle/mod/charon`  

### actually install charon
`Working directory: /bitnami/moodle/mod/charon` 
```
cp -p .env.develop.env
php composer.phar install --dev
apt install -y build-essential libpng-dev
npm install
npm run dev
```

### set permissions 
`Working directory: ~/WebstormProjects/charon/`

Run `sudo chmod -R 777 plugin/storage`  


## 3. Preparing Moodle for development

### create a bunch of users
You will need some users to create charons for
Go to **Site administration > Users > Accounts > Add a new user**  
and add some users there. # todo add a csv file with users

### create a course
Go to **Site home > \*cog\* > Turn editing on >** add a new course button appears  
Course info is up to you, **except** for name and short name. Those should both be `python-2021`  
In **Tags** section, add the tag `Programming`

### connect to docker container
Run `docker exec -ti charon-moodle bash`  
Go to charon folder `cd bitnami/moodle/mod/charon`

### seeding the database
`Working directory: /bitnami/moodle/mod/charon`

Run `php artisan db:seed`  
Run `php artisan db:seed --class=CharonSeeder` and create 2 charons there  
Your course id should be `2`  
Run `php artisan db:seed --class=SubmissionSeeder` and create some submissions    
Charon ids start from `1`, style test results should be `100`  
Now you should be able to access teachers ui in one of your charons by pressing **Charon popup** button

## 4. Connecting to the database

# tbd

