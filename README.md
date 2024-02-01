# SCiO Datascribe

The Datascribe project back-end.

## REQUIREMENTS

The project requires **[PHP 8.0](https://www.php.net/manual/en/install.php)** as well as **[Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)** to be installed.

## INSTALL THE DEPENDENCIES TO USE SAIL

    composer install --ignore-platform-reqs

## CONFIGURE

    cp .env.example .env

## RUN THE BACKEND

    ./vendor/bin/sail up -d

## RE-INSTALL THE CORRECT DEPENDENCIES

    ./vendor/bin/sail bash
    rm -rf vendor && composer install
    exit

## GENERATE A KEY AND A JWT SECRET

    ./vendor/bin/sail artisan key:generate
    ./vendor/bin/sail artisan jwt:secret

## RUN THE MIGRATIONS

To destroy the existing database and start fresh:

    ./vendor/bin/sail artisan migrate:fresh

## SEED THE DATABASE

To seed the database with test data:

    ./vendor/bin/sail artisan db:seed

**NOTE**: In order to be able to seed the data, you need to modify your **.env** file and set your application environment to the following `APP_ENV=local` or `APP_ENV=development`.

You can login using the following user account:

    - Username: **datascribe@scio.systems**
    - Password: **scio**

### LINK THE STORAGE FOR FILES

To link the storage for files:

    ./vendor/bin/sail artisan storage:link

**NOTE**: The storage for files should be linked in order for the backend to be able to serve files publicly.

### CHANGE THE QUEUE DRIVER TO REDIS (OPTIONAL)

To change the queue driver to redis, add the following settings:

    QUEUE_CONNECTION=redis
    REDIS_HOST=redis

## RUN TESTS

To execute the test suites run:

    ./vendor/bin/sail artisan test

## STOP THE BACKEND

    ./vendor/bin/sail down

## AUTOCOMPLETION

If you add a dependency, `_ide_helper.php` will be auto-regenerated. If you change a model definition please run:

    php artisan ide-helper:models

and type `no` to update the model autocompletion file (`_ide_helper_models.php`).
