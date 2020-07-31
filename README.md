# Saito

## What is it?

Saito is a web-forum with [conversation threading][ConversationThreading]. It is different from the majority of other forums as it puts the emphasis on performance and presenting conversations in a classic tree-style threaded view.

A lot of optimization went into serving long existing, small- to mid-sized communities with moderate traffic but hundreds of thousands of existing postings. It is able to displays hundreds of individual postings on a single page while running on a inexpensive, shared hosting account.

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

- PHP 7.2+ (extensions: gd, exif, intl, mbstring, pdo, simplexml)
- Database (MySQL/MariaDB tested, [others untested](https://book.cakephp.org/3.0/en/orm/database-basics.html#supported-databases)).

## Get Started

A ready-to-use ZIP containing all necessary files is available on the [release page](https://github.com/Schlaefer/Saito/releases). Unzip it, upload it to your server, open it in a browser, and follow the instructions on the screen.

## Development

### Set-Up Environment

You need a more or less generic environement providing:

-  PHP with `composer` for the server-backend (mainly build on [CakePHP][cake])
-  node with `yarn` and `grunt-cli` for the browser-frontend (mainly build on [Marionette][marionette])
-  a database

There's a docker file for *development* in `dev/docker/…`

### Install Files

Checkout the files from git-repository and install the dependencies:

```shell
composer install;
yarn install;
```

Move dependency-assets into the right places:

```shell
grunt dev-setup
```

Run all test cases:

```shell
composer test-all
```

See the `Gruntfile`, `packages.json` and `composer.json` for additional devleopment-commands.

### Create Production Files

To generate all the minimized assets for production:

```shell
grunt release
```

### Create A Release Zip

To generate a zip-package as found on the release page for distribution:

```shell
vendor/bin/phing
```

## FAQ

### How does it compare to [mylittleforum]

Actually this forum was written to replace a mylittleforum installation with a more modern approach. Mylittleforum is a noteworthy starting place if you want a threaded web-forum. There aren't that many out there. Mylittleforum exists for many years now and offers great features.

*Disclaimer: Subjective opinion ahead…*

But there are a shortcommings, mainly: performance and maintainability. If a mylittleforum installation reaches a few hundred thousand postings it is going to slow down. Also it was written when PHP was a much worse language: there are no test cases, which makes it more fragile to changes.

[mylittleforum]: https://mylittleforum.net/
