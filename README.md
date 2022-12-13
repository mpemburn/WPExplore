## WPExplore

A Laravel project to explore and manipulate WordPress multisite databases.

### Features:

**Subsite cloning**
This feature allows you to specify a particular subsite by its blog ID, and clone it from one database to another.  For example:

`php artisan clone:site --source=staging --dest=production --prefix=blog_ --blog_id=71`

This will copy all of the tables prefixed with `blog_71` from the `production` database to `staging`.  It will also copy the appropriate record from the source **blogs** table to that on the destination.  **NOTE:** If you don't specify a `--prefix`, it will default to `wp_`.
