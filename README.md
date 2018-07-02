# Saito

## What is it?

Saito is an open source classic threaded web forum written atop of [CakePHP][cake].

[Test it here][SaitoSupport] (login: test/test).

For more information please [visit the homepage][SaitoHomepage] or the see the `docs/` directory.

## Status

[![Build Status](https://secure.travis-ci.org/Schlaefer/Saito.png?branch=master)](http://travis-ci.org/Schlaefer/Saito)

[cake]: http://cakephp.org/
[SaitoHomepage]: http://saito.siezi.com/
[SaitoSupport]: http://saito.siezi.com/forum/

## Get Started

You need composer and yarn. Clone the repository:

```shell
git clone <saito5-repository> .
```

Install PHP dependencies

```shell
composer install --no-dev
```

At this stage you're able to run the forum.

## Get Started with Development

The git branching is following the gitflow model. PHP coding standards are generally following CakePHP.

Install the PHP development packages:

```shell
composer install
```

and the Javascript-packages setup:

```shell
grunt dev-setup
```

Create minimized assets with:

```shell
grunt release
```

Run all test cases:

```shell
composer test-all
```

See `packages.json` and `composer.json` for additional scripts to run.

To create a release-zip push the changes to the repository and run:

```shell
vendor/bin/phing
```
