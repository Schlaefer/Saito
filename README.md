# Saito

## What is it?

Saito is an open source classic threaded web forum written atop of [CakePHP][cake]. It performs well even on a modest server.

[Test it here][SaitoSupport] (login: test/test).

For more information please [visit the homepage][SaitoHomepage] or the see the `docs/` directory.

## Status

[![Build Status](https://secure.travis-ci.org/Schlaefer/Saito.png?branch=master)](http://travis-ci.org/Schlaefer/Saito)

[cake]: http://cakephp.org/
[SaitoHomepage]: http://saito.siezi.com/
[SaitoSupport]: http://saitotest.bplaced.net/saito/

## Requirements

- PHP 7.1
- Database (MySQL/MariaDB tested, [others untested](https://book.cakephp.org/3.0/en/orm/database-basics.html#supported-databases)).

## Get Started

A full prepackaged zip if is available on the [release page](https://github.com/Schlaefer/Saito/releases).

## Development

### Install Files

Checkout files from git.

Install the PHP packages:

```shell
composer install
```

Install Javascript packages:

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
