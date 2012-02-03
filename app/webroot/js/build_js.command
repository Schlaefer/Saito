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
scripts[2]='jquery.form.js'
scripts[4]='jquery.clearabletextfield.js'

for i in "${scripts[@]}"
do
  cat $i >> custom_javascript.js 
done

### Compress ###
yuicompressor 'custom_javascript.js -o custom_javascript-compressed.js'

### Merge compressed files ###
cat jquery.hoverIntent.minified.js jquery-ui-1.8.13.custom.min.js custom_javascript-compressed.js  jquery.scrollTo-1.4.2-min.js > js.min.js  

### Clean Up Temp Files ###
rm classes.min.js
rm custom_javascript.js
rm custom_javascript-compressed.js
