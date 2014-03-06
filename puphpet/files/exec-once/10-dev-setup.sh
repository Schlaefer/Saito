#!/bin/bash

### install PHP Code Sniffer
# limit to 1.5.1 because of http://pear.php.net/bugs/bug.php?id=20196
sudo pear install PHP_CodeSniffer-1.5.1;

### install CakePHP's Code Sniffer flavor
sudo pear channel-discover pear.cakephp.org;
sudo pear install cakephp/CakePHP_CodeSniffer;

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
composer install;

### run Saito dev setup
grunt dev-setup;

# init vagrant database config
cp /vagrant/app/Config/database.php.vagrant /vagrant/app/Config/database.php;
