#!/bin/sh

# fix failing installs because of server not found
sudo echo "nameserver 8.8.8.8" > /etc/resolv.conf;

