# Charon

Moodle assignment module for programming tasks. Integrated with automated tester. 


## Requirements

* Moodle installed
* PHP7
* NodeJS (for development)


## Set up

```bash

# Clone the repository into mod/ folder
git clone git@gitlab.com:ained/charon.git
cd charon

# Install PHP dependencies
php composer.phar install

# Configure PHP, Laravel
cp -p .env.example .env
php artisan key:generate

# Install JavaScript dependencies if developing
npm install

# Install module in Moodle (creates database)

```
