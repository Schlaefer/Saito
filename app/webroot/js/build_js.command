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

for i in "${scripts[@]}"
do
  cat $i >> custom_javascript.js 
done

### Compress ###
yuicompressor 'custom_javascript.js -o custom_javascript-compressed.js'

### Merge compressed files ###
cat jquery.hoverIntent.minified.js lib/jquery-ui/jquery-ui-1.8.22.custom.min.js custom_javascript-compressed.js  jquery.scrollTo-1.4.2-min.js > js.min.js

### Clean Up Temp Files ###
rm classes.min.js
rm custom_javascript.js
rm custom_javascript-compressed.js

#*******************************************************************************
# new backbone config
#******************************************************************************/

# Merging uncompressed files
version2[0]='_appbb.js'

for i in "${version2[@]}"
do
  cat $i >> custom_javascript2.js
done

### Compress ###
yuicompressor 'custom_javascript2.js -o custom_javascript-compressed2.js'

### Merge compressed files ###
cat lib/underscore/underscore-min.js lib/backbone/backbone-min.js lib/backbone/backbone.localStorage-min.js bootstrap/bootstrap.min.js custom_javascript-compressed2.js > js2-min.js

### Clean Up Temp Files ###
rm custom_javascript2.js
rm custom_javascript-compressed2.js

#******************************************************************************/

