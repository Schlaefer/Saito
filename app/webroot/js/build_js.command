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

for i in "${scripts[@]}"
do
  cat $i >> custom_javascript.js
done

### Compress ###
yuicompressor 'custom_javascript.js -o custom_javascript-compressed.js'

### Merge compressed files ###
cat lib/jquery-ui/jquery-ui-1.9.2.custom.min.js custom_javascript-compressed.js > js.min.js

### Clean Up Temp Files ###
rm classes.min.js
rm custom_javascript.js
rm custom_javascript-compressed.js

### rquire.js optimizer ###
node lib/r/r.js -o build.js

