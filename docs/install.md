You need
========

- Webserver (tested with Apache and nginx)
- A DB (tested with MySQL 5+).
- PHP 5.2+
    - php.ini activate PHP-shortags

If you are stuck
================

If something goes wrong enable the CakePHP debug mode, which gives you more informations. In `app/config/core` set:

	Configure::write('debug', 0);

to

	Configure::write('debug', 1);


Installation from git repository
================================


1. checkout repository
----------------------

Make sure you have a [github](http://github.com/) account, because you have to checkout an submodule from github.


2. create database
------------------

Create the database where the tables for your shiny new forum will live. Use collation to `utf8_general_ci` (or other appropriate utf8 encoding).

3. Setup CakePHP Security Features
----------------------------------

In `core.php` set to a long random string:
    
    Configure::write('Security.salt', 'replacewhendeploy');
    
and with a long numerical string:

    Configure::write('Security.cipherSeed', '1234567890');

to something nice and random.

4. open root url
----------------

- Apache: `.htaccess` should take care of correct url, just open the url root.
- nginx: You have to open `../app/webroot` manually until you configure nginx. See section [Nginx Configuration for CakePHP](#NginxConfigurationForCakePHP).


Hotfix
------

Delete

    , 'fulltext_search' => array('column' => array('subject', 'text', 'name'), 'unique' => 0)
    
from app/config/schema/schema.php


5. open the installer
---------------------

Open the URL root and if everything went OK the installer should greet you.

5.1 tmp dir is not writeable
----------------------------

    mkdir app/tmp
    mkdir app/tmp/logs
    mkdir app/tmp/cache
    mkdir app/tmp/cache/models
    mkdir app/tmp/cache/persistent
    mkdir app/tmp/cache/views

    
The `tmp`-Directory and its subdirectories should be writable by the webserver. The rights wisely set you must young padawan.

    
5.2 config dir is not writable
------------------------------

app/config folder needs to be writable by the webserver during installation
    
    
5.2 Enter DB data
-----------------

Enter the database data. If data is fine create database.


Nginx Configuration for CakePHP <a name="NginxConfigurationForCakePHP"/>
===============================


