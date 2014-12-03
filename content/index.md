<!--
Title: Saito
Template: home
-->

# +++ Newsflash +++ #

<div class="text-muted"><small >2014-12-03</small></div>

At the moment the upgrade to CakePHP 3.0 is underway. This is a considerable amount of work and may take several months. Every help is appreciated, please contact to get involved.

# What is it #

Saito is an open source threaded web forum written in PHP (see [features](#features) & [tech specs](#techspecs)).

Test the [demo install][testforum] (login: test/test) or see production sites like [macnemo.de] or [macfix.de].

# Why #

We had the need for a classic threaded forum but with modern, maintainable and extendable source.

It should be deployable and scale admirably on modest shared hosting accounts.

# Nitty-Gritty #

<a name='features'></a>

## Features ##

- extendable via [plugins](saito-plugins)
- different views: "threaded", "mix" and "inline"
- default markup is BBCode (use different markups via plugins)
- embed rich content
	- smilies
	- html5 audio & video 
	- flash & iframe video
	- embed.ly support
	- code highlighting
	- image upload
- mark entries read
	- server side managed for registered members
	- client side (cookie based) for unregistered visitors
- API
- mobile web-app
- email notifications on replies
- roles: administrators, moderators and normal users
- chat-box
- community map
- categories
- theming
- localization via language files
- anti-spam measures
- SEO
	- structured data ([schema.org](http://schema.org/))
	- sitemap
- RSS-feed


<a name='techspecs'></a>

## Tech Specs ##

- requires PHP 5.4+ and a database (MySQL recommended)
- PHP framework: [CakePHP]
- Frontend: backbone.js/marionette.js
- BBCode parser: [jBBCode]


[CakePHP]: http://cakephp.org/
[jBBCode]: http://bbcode.com
[Marionette]: http://marionettejs.com/
[macnemo.de]: http://macnemo.de/
[macfix.de]: http://www.macfix.de/
[testforum]: http://saito.siezi.com/forum/