## Time keeper

A tool to keep track of the time spend in tasks related to projects

## Requirements

-   php 8.0 - 8.2
-   node v16.20.2

## Frameworks used

-   Laravel 9
-   Vue 2

## Setup Instructions

-   Setup you database connection with .env file
-   You may run the commands "composer install" and "npm install" to install php and javascript dependencies
-   Make the migration with php artisan migrate
-   Check the the file "database\seeders\DatabaseSeeder.php" to check the default credentials
-   Run the command php artisan db:seed
-   Run the following commands:
    php artisan passport:keys
    php artisan key:generate
    php artisan passport:install
    php artisan config:clear

## Use Instructions

-   Create the users you need
-   Create the teams
-   Create the projects
-   Report the time as needed
