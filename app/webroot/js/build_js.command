#! /bin/bash
source ~/.bashrc

### Setup ###
scriptdir=`dirname $BASH_SOURCE`
cd $scriptdir

### Load Classes ###
for filename in classes/*
do
  cat $filename >> classes.min.js
done;

### Merging uncompressed files ###
scripts[0]='classes.min.js'
scripts[1]='_app.js'

for i in "${scripts[@]}"
do
  cat $i >> custom_javascript.js 
done

### Compress ###
yuicompressor 'custom_javascript.js -o custom_javascript-compressed.js'

### Merge compressed files ###
cat jquery.hoverIntent.minified.js lib/jquery-ui/jquery-ui-1.8.22.custom.min.js custom_javascript-compressed.js  lib/jquery.scrollTo/jquery.scrollTo-min.js > js.min.js

### Clean Up Temp Files ###
rm classes.min.js
rm custom_javascript.js
rm custom_javascript-compressed.js
