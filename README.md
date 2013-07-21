# What is this?

This is the codebase that originally ran the website SparkRebel.com.
The site has been discontinued but this code has been made available for others to view and (hopefully) learn from.

## Requirements

 * PHP 5.3.3+
 * MongoDB
 * Redis
 * ElasticSearch (optional)
 * Gearman (optional)

## Setting Up Local Environment

 * `cp app/config/parameters.yml.dist app/config/parameters.yml`
   * Edit parameters as needed
 * Download/install [Composer](http://getcomposer.org/download/)
   * `curl -s https://getcomposer.org/installer | php`
 * `php composer.phar install --dev`

### Loading a mongodump

 * `mongorestore -d {dbname} path/to/dump`
