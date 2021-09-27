# Charon and Moodle local setup and development

Intention of the following document is to make it easier to get up and going with developing Charon in localhost.
By default, it's complicated to get Charon to work with fresh Moodle. By following this tutorial, you're 15 minutes
apart from perfectly working 1:1 development environment which is running at https://moodle.taltech.ee/.

## Prerequisites

This guide assumes that you have installed PHP 7.3, [docker](https://docs.docker.com/get-docker/) and [docker-compose](https://docs.docker.com/compose/install/),
are familiar with command line and have read access to [Charon repository](https://gitlab.cs.ttu.ee/ained/charon).

#### Notes on PHP setup

mac: https://www.youtube.com/watch?v=gbWfA87yh3Q&t=2s

windows: https://www.youtube.com/watch?v=oGMDpMNFFn4&t=1s

After downloading php for win, unpack php files to some folder where folder name does not contain spaces (program files does not work, directly on c: ok).
When adding variable to path, php.exe file should be directly under the lastly referenced folder, or it is not discoverable.
PHP has by default php.ini-development and php.ini-production files under the main folder. 
Copy one of them end change to php.ini. When modifying this file, things should work.

## First time setup

### Get Charon

```bash
git clone https://gitlab.cs.ttu.ee/ained/charon && cd charon
```

### Install moodle

Moodle instance will run inside a docker container, your local Charon folder will be mounted into that container
at `bitnami/moodle/mod/charon` - hence your (PHP code) changes will have immediate effect.

Navigate to `/docs` and start the Moodle and its database container with

```bash
docker-compose up -d
```

Moodle container may take a few minutes to start up initially, you can follow its progress and verify its working shorty
once the logs have passed `Starting Apache...` with

```bash
docker logs -f charon-moodle
```

### Install Charon

During the first installation of Charon you need a few extra steps to get the plugin running. Connect to the container
with your command line:

```bash
docker exec -it charon-moodle bash
```

Navigate to Charon directory and execute the following commands

```bash
cd bitnami/moodle/mod/charon
./dev-setup.sh
```

Script `dev-setup.sh` is executing following commands:

```bash
git config core.filemode false
git checkout -- .htaccess
php composer.phar install
npm config set registry https://registry.npmjs.org/
npm ci
npm run dev
sudo chmod -R 777 plugin/storage/
```

If you already have set up development environment before, and only need to refresh some parts, not all these commands are really neccessary. Usually running just few of them is ok.

### Login to Moodle

Navigate to [http://localhost](http://localhost) and login with user `dev` and password `dev`.

After initial login, on the bottom of the page click "Continue" and on the following page there is a possibility to update data, 
after which database tables for charon should be created.

### Verify that the database installation has been successful

Initial data creation may sometimes fail during the installation. To verify if it succeeded logon to the local database
(see **Using Adminer** section below) and see if the `mdl_charon_preset` table has more than zero entries.

If the table is empty, run the following command in the Charon directory _inside the Moodle container_ (see **Install Charon** above)

```bash
php artisan db:seed
```

### Revert possible changes to .htaccess

Moodle container sometimes may want to overwrite your local `.htaccess` which came with the project.
In your project root, check `git status` and if the `.htaccess` file appears to be modified then discard the changes

```bash
git checkout -- .htaccess
```

### Installing TalTech theme

Ask access to the theme repository or a direct zip file for the `Taltech Boost` theme in our chat.

Copy the theme inside the Moodle container
```
docker cp <theme_folder> charon-moodle:bitnami/moodle/theme/<theme_folder>
```

Afterwards, navigate to your Moodle instance from browser. You should be prompted with new plugin installation page.

Make TalTech theme default by navigating to **Site administration > Appearance > Theme > Theme selector > Default > Change theme**.

Select **Taltech Boost** from the list.


## Working with Charon locally

### Debug mode

You can actually get much more logs from your moodle than now. Go to **Site administration > Development > Debugging**  
and set **Debug messages** to `DEVELOPER`

### Updating frontend source in Moodle container

In order to update the frontend components run any of the following commands in the Charon directory inside the Moodle
container (see [Webpack CLI docs](https://webpack.js.org/api/cli/) for additional information).

```bash
npm run dev
npm run watch
npm run hot
```

### Creating users in Moodle

To add users go to **Site administration > Users > Accounts > Add a new user**.

### Creating a course

Go to **Site home > \*cog\* > Turn editing on >** add a new course button appears.

Course info is up to you, but the shortname should follow a similar pattern `python-2021`.

In **Tags** section, add the tag `Programming`

### Create Charons and Submissions

To create new Charons or Submissions run either of the following commands in
the Charon directory _inside the Moodle container_ (see **Install Charon** above).

The seeders will prompt you for input for which you can find values in the database.

```bash
php artisan db:seed --class=CharonSeeder
php artisan db:seed --class=SubmissionSeeder
```

### Using Adminer

Docker-composes also have an Adminer container for easy access to database.

To use it, navigate to [http://localhost:8190/](http://localhost:8190/) and login with the following parameters:
- System: `MySQL`
- Server: `mariadb:3306`
- Username: `root`
- Password: `root`
- Database: `bitnami_moodle`

## Misc

### Why .htacces is used

.htaccess is needed so `/mod/charon/courses/<id>/settings` doesn't return 404. Purely because how Moodle is done

### File permissions

By default, git picks up changes in [file permissions](https://linuxhandbook.com/linux-file-permissions/).      
During the development process, Moodle might change the file permissions on your machine.  
Run `git config core.filemode false` to disable tracking of filemode changes for this project.

### Using "npm install"

If you need to reinstall components for gui (in `node_modules` folder), use **`npm ci`**,  not `npm install`.
As rule, you should not commit changes in `package-lock.json` file, if it was changed accidentally.
Only if `package.json` is modified on purpose, execute `npm update` or `npm install` and after this also commit new `package-lock.json`.

### Reinstall with full cleanup

If you need to clean up containers and start from scratch, remember that probably you need to remove all Containers, Images 
and Volumes before creating containers again. Volumes are containing permanent info, like database content, for example.
