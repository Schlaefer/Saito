# Setup Dev Environment #

## Via Vagrant ##

It's recommended to use Vagrant to setup the dev-VM:

1. [Install Vagrant][Vagrant Download]
2. [Install Virtual Box][VirtualBox Download]
3. [Clone/download Saito][Saito Dev Download]
3. run `vagrant up` in Saito's root directory

If `vagrant up` fails on the initial run try to provision again: `vagrant shutdown; vagrant up; vagrant provision`.

The VM's IP is `192.168.2.250`. Add `192.168.2.250 saito.dev` to your local host file and open <http://saito.dev> in your browser. MailCatcher is available at <http://saito.dev:1080/>.

To ssh into Saito's app-root use `vagrant ssh`.


## Via Manual Setup ##

You must at least run `composer install`. For a full dev setup refer to the scripts in `puphpet/files/exec-ones`.


# Conventions #

The git branching is following the [gitflow][gitflow] model. PHP coding standards are generally following [CakePHPs][php-coding-standards].

# Running Test Cases #

Run all CLI tests from the root:

	vagrant ssh
	grunt test

## CakePHP Tests ##

Run in the browser: <http://saito.dev/test.php> (debug mode set in `core.php` must be at least `0`);

Run on CLI:

	grunt test:php

## PHPCS ##

	grunt test:phpcs

## JS Tests ###

	grunt test:js

# Release #

## Create Production Files ##

Run `grunt release` to create minimized files used in production mode (`debug = 0`).

## Create End-User Distribution Archive ##

1. push release build to github master.
2. run `phing` to create `dist/saito-*.zip` from master.



[Vagrant Download]: http://www.vagrantup.com/downloads
[VirtualBox Download]: https://www.virtualbox.org/wiki/Downloads
[Saito Dev Download]: https://github.com/Schlaefer/Saito
[gitflow]: http://nvie.com/posts/a-successful-git-branching-model/
[php-coding-standards]: http://book.cakephp.org/2.0/en/contributing/cakephp-coding-conventions.html

