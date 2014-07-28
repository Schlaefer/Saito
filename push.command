#!/usr/bin/env bash

cd "$(dirname "$0")"
git add -A .;
read -p "Git commit message (q to abort): " gm;
case $gm in 
   [q]*) exit;;
esac
git commit -m "$gm"
git push origin gh-pages;
mina deploy
