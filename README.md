# Saito

## What is it?

Saito is a web forum. It is different from the majority of other solutions as it puts the emphasis on presenting threads and conversations in a classic tree style view. It is optimized to display hundreds of individual posts on a single page request while running on a modest shared-hoster.

[Test it here][SaitoSupport] (log-in: test/test).

## Status

[![Build Status](https://secure.travis-ci.org/Schlaefer/Saito.png?branch=master)](http://travis-ci.org/Schlaefer/Saito)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Schlaefer/Saito/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Schlaefer/Saito/?branch=develop)

[cake]: http://cakephp.org/
[marionette]: https://marionettejs.com/
[SaitoHomepage]: http://saito.siezi.com/
[SaitoSupport]: http://saito-forum.de/

## Requirements

- PHP 7.2
- Database (MySQL/MariaDB tested, [others untested](https://book.cakephp.org/3.0/en/orm/database-basics.html#supported-databases)).

## Get Started

A full prepackaged zip if is available on the [release page](https://github.com/Schlaefer/Saito/releases).

## Development

### Install Files

Checkout files from git.

Install the PHP packages (the backend is mainly build on [CakePHP][cake]):

```shell
composer install
```

Install Javascript packages (the frontend is mainly build on [Marionette][marionette]):

```shell
yarn
```

Move files into places:

```shell
grunt dev-setup
```

Run all test cases:

```shell
composer test-all
```

See `Gruntfile`, `packages.json` and `composer.json` for additional scripts to run.

### Create Production Files

Create minimized assets with:

```shell
grunt release
```

Create a release-zip:

```shell
vendor/bin/phing
```
