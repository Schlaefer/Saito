#!/usr/bin/env bash

cd "$(dirname "$0")"
git add .;
git commit -m 'gh-page update (auto)';
git push origin gh-pages;
