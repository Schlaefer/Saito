#!/bin/bash

### Phing
sudo pear channel-discover pear.phing.info;
sudo pear install --alldeps phing/phing;

### install compass
# use >3.3
sudo gem install sass --pre;
# use compass >0.12
sudo gem install compass --pre;
sudo gem install sass-css-importer --pre;

### install node
sudo echo "deb http://ftp.us.debian.org/debian wheezy-backports main" >> /etc/apt/sources.list;
sudo apt-get -y install nodejs-legacy;

### install npm
# `clean=no` see: http://stackoverflow.com/questions/20174399/cannot-install-npm-on-vagrant-during-provision
curl https://www.npmjs.org/install.sh | sudo clean=yes sh;

### install grunt
sudo npm install -g grunt-cli;

### install bower
sudo npm install -g bower;

### install local packages
cd /var/www;

### install local npm packages
# needed in 12.4 for bower package "phantom.js"
sudo apt-get -y install libfontconfig1;
npm install;

### install local composer packages
composer install -d app/;
# lets phpcs know where to find CakePHP's sniffs
app/Vendor/bin/phpcs --config-set installed_paths app/Vendor/cakephp/cakephp-codesniffer


### install siege for Cake siege console task
sudo apt-get -y install siege;

### run Saito dev setup
grunt dev-setup;

# set vagrant database config
cp /vagrant/app/Config/database.php.vagrant /vagrant/app/Config/database.php;
# set vagrant email config
cp /vagrant/app/Config/email.php.vagrant /vagrant/app/Config/email.php;
