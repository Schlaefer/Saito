# Saito

## What is it?

Saito is a web-forum with [conversation threading][ConversationThreading].

It is different from the majority of other solutions as it puts the emphasis on performance and presenting conversations in a classic tree style threaded view.

It is optimized to to serve long existing, small- to mid-sized communities with posting numbers reaching into the 1+ million. It displays hundreds of individual postings on a single page request while running on a inexpensive, shared hosting account.

[Test it here][SaitoSupport] (login: test/test).

## Status

[![Build Status](https://secure.travis-ci.org/Schlaefer/Saito.png?branch=master)](http://travis-ci.org/Schlaefer/Saito)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Schlaefer/Saito/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Schlaefer/Saito/?branch=develop)

[cake]: http://cakephp.org/
[marionette]: https://marionettejs.com/
[SaitoHomepage]: https://saito.siezi.com/
[SaitoSupport]: https://saito-forum.de/
[ConversationThreading]: https://en.wikipedia.org/wiki/Conversation_threading

## Requirements

- PHP 7.2+
- Database (MySQL/MariaDB tested, [others untested](https://book.cakephp.org/3.0/en/orm/database-basics.html#supported-databases)).

## Get Started

A ready-to-use zip if is available on the [release page](https://github.com/Schlaefer/Saito/releases). Unzip it, upload it to your server and follow the instructions on the screen.

## Development

### Set-Up Environment

You need a more or less generic PHP, node, and DB environment. There's a docker file for *development* in `dev/docker/...`.

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

## FAQ

### How does it compare to mylittleforum

Actually this forum was written to replace a mylittleforum installation with a more modern approach. Mylittleforum is a noteworthy starting place if you need threaded web-forum. There aren't many out there. Mylittleforum exists for many years now and offers great features. But there are a shortcommings, mainly: performance and maintainability.

If a mylittleforum installation reaches a few hundred thousand postings it inevitably is going to start to slowdown. It requires a lot of effort to scale mylittleforum beyong that point.

Mylittleforum was written when PHP was a much worse language (pre 5.x days). For example there are no test cases. Every change requires a thourough insight into its inner workings so you just know a simple change wont break functionality in another place.
