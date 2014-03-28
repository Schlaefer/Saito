# You need #

- Web server (tested with Apache and nginx)
- MySQL 5+ (tested with 5.1 and 5.5)
- PHP 5.4+
     - mcrypt enabled

# Installation #

## 1. Get the Code ##


### Via Download

Download the latest release from <http://siezi.saito.com/>.

### Via Composer

	composer create-project schlaefer/saito . dev-master`

## 2. Create Database ##

Create a database. Set collation to `utf8_unicode_ci` (or other appropriate utf8 encoding).

## 3. Open Root URL ##

- Apache: `.htaccess` should take care of correct url, just open the url root.
- nginx: You have to open `../app/webroot` manually until you configure nginx. See section [Nginx Configuration for CakePHP](#NginxConfigurationForCakePHP).

## 4. Installer ##

Open the URL root and if everything went OK the installer should greet you.

### 4.1 tmp Directory Is Not Writeable ###

The `tmp`-directory and its subdirectories have to be be writable by the webserver. Set the rights accordingly.

### 4.2 Config Directory Is Not Writable ###

The `app/config` folder needs to be writable by the webserver during the installation.


### 4.3 Connect to Database ###

Enter the database connection data into the web-installer. If the database connection is OK create the database.

Follow the web-installer's instruction to the end.

## After the installation

- ** Backup the app/Config directory! **
- Change the admin-account password to a secure phrase
- Set email addresses. [See config-email.md](config-email.md)
	- Set the admin-account email address (user preferences)
	- Set the forum email sender address (forum settings)
- You did make a backup of app/Config in step 1, right?

# Nginx Configuration for CakePHP <a name="NginxConfigurationForCakePHP"/> #

todo

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

Also see `app/tmp/logs/error.log`.

# Tips #

If you're able to symlink you should:

- symlink app/webroot/useruploads for easier update
- [symlink Theme assets to the webfoot folder](http://book.cakephp.org/2.0/en/views/themes.html#increasing-performance-of-plugin-and-theme-assets)

/* vim: set filetype=mkd : */
