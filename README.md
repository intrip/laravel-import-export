#Laravel-Import-Export

Import-Export is a package to import and export data from various format into a database.

- **Author**: Jacopo Beschi
- **Version**: 0.1.0

[![Build Status](https://travis-ci.org/intrip/laravel-import-export.png)](https://travis-ci.org/intrip/laravel-import-export)

<img src="https://raw.github.com/intrip/laravel-import-export/master/examples/import_step1.jpg" />

##Features

- Import and export data from Csv file into database
- Multi DBMS: works with all DBMS supported by Laravel ORM
- Create database schema: allow you to create database schema when importing data

##Under the box: features incoming in 0.2.0

- Import and export an arbitrary number of lines
- Import and export JSON and XML
- Database access configurabile with a GUI

## Requirements

- Php >= 5.3.7
- Composer
- Laravel framework 4.0.*
- DBMS that support transactions and supported by Laravel ORM

##Installation with Composer

To install Import-Export with Composer, simply add this to your composer.json in the require field of your laravel app:

```json
"jacopo/laravel-import-export": "dev-master"
```
After you need to execute the following commands:

```php
php artisan config:publish jacopo/laravel-import-export
php artisan asset:publish jacopo/laravel-import-export
```
Now you have under `app/config/packages/jacopo/laravel-import-export` the package configuration files. At this point you need to configure the database access. Open the file database.php and update it with the database access information. When done run the following command to initialize ImportExport database.

```php
php artisan migrate --package="jacopo/laravel-import-export" --database="import"
```
This command will create a _import_export_temporary_table in the db, you can change the name of the table editing the the key: `table_prefix` under the file `app/config/packages/jacopo/laravel-import-export/baseconf.php`.

Congratulations! Now you can view the application at the url: `http://url-of-your-baseapp/importer`. If needed you can change the base route editing the the key: `base_application_route` under the file `app/config/packages/jacopo/laravel-import-export/baseconf.php`.
