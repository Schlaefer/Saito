#!/usr/bin/env bash

cd "$(dirname "$0")"
phr up;
git add .;
git commit -m '#';
git push origin master;
