#!/usr/bin/env bash

cd "$(dirname "$0")"
git diff --name-status
read -p "Git commit message (q to abort): " gm
case $gm in 
   [q]*) exit;;
esac
git add -A .;
git commit -m "$gm"
git push origin gh-pages;
mina deploy
