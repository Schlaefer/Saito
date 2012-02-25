# You need #

- Web server (tested with Apache and nginx)
- MySQL 5+
- PHP 5.3+ (if you're running your own server: in php.ini activate PHP-shortags)


# Installation #

## 1. Get the Code ##

Checkout git repository or download a zip from github.

## 2. Create Database ##

Create the database where the tables for your shiny new forum will live. Use collation to `utf8_general_ci` (or other appropriate utf8 encoding).

## 3. Open Root URL ##

- Apache: `.htaccess` should take care of correct url, just open the url root.
- nginx: You have to open `../app/webroot` manually until you configure nginx. See section [Nginx Configuration for CakePHP](#NginxConfigurationForCakePHP).

## 5. Open the Installer ##

Open the URL root and if everything went OK the installer should greet you.

### 5.1 tmp Directory Is Not Writeable ###

The `tmp`-directory and its subdirectories have to be be writable by the webserver. The rights wisely set you must young padawan.

    
### 5.2 Config Directory  Is Not Writable ###

The `app/config` folder needs to be writable by the webserver during the installation.
    
    
### 5.3 Connect to Database ###

Enter the database connection data. If the database connection is OK then create the database in the web-installer.

Follow the web-installer's instruction to the end.


# Nginx Configuration for CakePHP <a name="NginxConfigurationForCakePHP"/> #

@todo

# If you are stuck #

If something goes wrong enable the CakePHP debug mode, which shows you more information. In `app/config/core.php` set:

	Configure::write('debug', 0);

to

	Configure::write('debug', 1);
