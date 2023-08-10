# WP Explore

A Laravel project to explore and manipulate WordPress multisite databases.

## Requirements:
**WP Explore** was created in **Laravel version 9.x** and requires the following:

* **PHP** >= 8.1
* **Composer** >= 2.5.5
* **npm** >= 9.7.2

## Installation:
Install **WP Explore** locally with the following command:

`git clone git@github.com:mpemburn/WPExplore.git`

Change to the `WPExplore` directory and run:

`composer install`

...to install the PHP dependencies.

`npm install`

...to install modules needed to compile the JavaScript and CSS assets.

`npm run build`

...to do the asset compiling.

You will need to run a web server to run **WP Explore** in a browser.
I recommend [**Laravel Valet**](https://laravel.com/docs/10.x/valet), but you can do it simply by going to the project
directory and running:

`php artisan:serve`

This will launch a server on `http://127.0.0.1:8000`

## Features:

### Database Searching

**WP Explore** features a powerful WordPress database searching tool.  With it you can search all subsites on:
* Posts
* Postmeta
* Option Values
* Option Names
* Shorcodes in Posts

In order to use this feature, you must have at least one WordPress database installed locally.
The database(s) need to be defined in your `.env` file as follows:

`INSTALLED_DATABASES="Database 1:my_first_db,Database 2:my_second_db"`


### Subsite Cloning
This feature allows you to specify a particular subsite by its blog ID, and clone it from one database to another.  For example:

`php artisan clone:site --source=staging --dest=production --prefix=blog_ --blog_id=71`

This will copy all of the tables prefixed with `blog_71` from the `production` database to `staging`.  It will also copy the appropriate record from the source **blogs** table to that on the destination.  **NOTE:** If you don't specify a `--prefix`, it will default to `wp_`.
