#! /bin/bash
source ~/.bashrc

### Setup ###
scriptdir=`dirname $BASH_SOURCE`
cd $scriptdir

### rquire.js optimizer ###
node lib/r/r.js -o build.js paths.requireLib=lib/require/require.min include=requireLib

