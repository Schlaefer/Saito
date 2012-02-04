# You need #

- Webserver (tested with Apache and nginx)
- MySQL 5+
- PHP 5.2+
    - If you're running your own server: in php.ini activate PHP-shortags

# If you are stuck #

If something goes wrong enable the CakePHP debug mode, which shows you more information. In `app/config/core.php` set:

	Configure::write('debug', 0);

to

	Configure::write('debug', 1);


# Installation from git repository #

## 1. checkout repository ##

Make sure you have a [github](http://github.com/) account, because you have to checkout an submodule from github.


## 2. create database ##

Create the database where the tables for your shiny new forum will live. Use collation to `utf8_general_ci` (or other appropriate utf8 encoding).

## 3. open root url ##


- Apache: `.htaccess` should take care of correct url, just open the url root.
- nginx: You have to open `../app/webroot` manually until you configure nginx. See section [Nginx Configuration for CakePHP](#NginxConfigurationForCakePHP).


## Hotfix ##

Delete

    , 'fulltext_search' => array('column' => array('subject', 'text', 'name'), 'unique' => 0)
    
from app/config/schema/schema.php


## 5. open the installer ##

Open the URL root and if everything went OK the installer should greet you.

### 5.1 tmp dir is not writeable ###

The `tmp`-Directory and its subdirectories have to be be writable by the webserver. The rights wisely set you must young padawan.

    
### 5.2 config dir is not writable ###

The `app/config` folder needs to be writable by the webserver during the installation.
    
    
### 5.3 Enter DB data ###

Enter the database data. If data is fine create database.

# Install from a zip #

With a little bit of luck something is available from <https://github.com/Schlaefer/Saito/downloads> and you don't need git. Proceed from step "2. create database".

# Nginx Configuration for CakePHP <a name="NginxConfigurationForCakePHP"/> #

See #panxatonylookingatthings.tumblr.com
