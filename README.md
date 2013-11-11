#laravel-import-export

Import-Export is a package to import and export data from various format into a database. Now only works with Csv files, but will add new format soon.

- **Author**: Jacopo Beschi
- **Version**: 0.1.0

##Features

- Import and export data from Csv file into database
- Multi DBMS: works with all DBMS supported by Laravel ORM
- Create database schema: allow you to create database schema when importin data

##Under the box: features incoming in 0.2.0

- Import and export an arbitrary number of lines
- Import and export JSON and XML
- Database access configurabile with a GUI
- Looking for another new feature? Contact me and i may implement that

## Requirements

php >= 5.3.7
composer
laravel framework 4.0.*
DBMS that support transactions

##Installation with Composer

To install Import-Export as a Composer package to be used with Laravel 4, simply add this to your composer.json in the require field:

```json
"jacopo/laravel-import-export": "dev-master"
```

metti screenshoot!!!!