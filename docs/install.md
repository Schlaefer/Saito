# You need #

- Web server (tested with Apache and nginx)
- MySQL 5+
- PHP 5.3+ 


# Installation #

## 1. Get the Code ##

Checkout git repository or download a zip from github. Usually it is recommended to use the last [tagged version](https://github.com/Schlaefer/Saito/tags).

## 2. Create Database ##

Create a database. Set collation to `utf8_general_ci` (or other appropriate utf8 encoding).

## 3. Open Root URL ##

- Apache: `.htaccess` should take care of correct url, just open the url root.
- nginx: You have to open `../app/webroot` manually until you configure nginx. See section [Nginx Configuration for CakePHP](#NginxConfigurationForCakePHP).

## 4. Installer ##

Open the URL root and if everything went OK the installer should greet you.

### 4.1 tmp Directory Is Not Writeable ###

The `tmp`-directory and its subdirectories have to be be writable by the webserver. Set the rights accordingly.
    
### 4.2 Config Directory  Is Not Writable ###

The `app/config` folder needs to be writable by the webserver during the installation.
    
    
### 4.3 Connect to Database ###

Enter the database connection data into the web-installer. If the database connection is OK create the database.

Follow the web-installer's instruction to the end.

## After the installation

- **Backup app/Config/core.php!**
- Change the admin password to a secure phrase
- Set email addresses so contacting the admin works:
	- Set the admin email address (user preferences)
	- Set the forum email address (forum settings)
- You made a backup of core.php in step 1, right?

# Nginx Configuration for CakePHP <a name="NginxConfigurationForCakePHP"/> #

@todo

# Running on your own server

## Suhosin

If you use suhosin set the following in you `php.ini`:

    suhosin.srand.ignore = Off
    suhosin.mt_srand.ignore = Off

(Symptoms if not set: you can't stay logged-in.)


# Troubleshooting

## Enable debug mode

If something goes wrong enable the CakePHP debug mode, which shows you more information. In `app/Config/core.php` set:

	Configure::write('debug', 0);

to

	Configure::write('debug', 1);


/* vim: set filetype=mkd : */ 
