#!/bin/sh

# sets rights for session creation in CakePHP CLI tests
sudo chmod -R  ugo+rwx  /var/lib/php;

# Cake alias
alias cake='/var/www/app/Console/cake'
