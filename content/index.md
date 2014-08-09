<!--
Title: Home
Template: home
-->

# What is it

Saito is an open source threaded web forum written in PHP (see [features](#features) & [tech specs](#techspecs)).

Test the [demo install][testforum] (login: test/test) or see production sites like [macnemo.de] or [macfix.de].

# Why

We had the need for a classic threaded forum but with modern, maintainable and extendable source.

It should be deployable and scale admirably on modest shared hosting accounts.

# Nitty-Gritty ##

<a name='features'></a>

## Features ##

- different views: "threaded", "mix" and "inline"
- BBCode
- embed rich content
	- smilies
	- html5 audio & video 
	- flash & iframe video
	- embed.ly support
	- code highlighting
	- image upload
- mark entries read
	- server side managed for registered members
	- client side (cookie based) for non-logged-in visitors
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
- flattr
- SEO
	- structured data (schema.org)
	- sitemap
- RSS-feed


<a name='techspecs'></a>

## Tech Specs ##

- requires PHP 5.4+ and a database (MySQL recommended)
- PHP framework: CakePHP
- Frontend: backbone.js/marionette.js
- BBCode parser: jBBCode

[CakePHP]: http://cakephp.org/
[Marionette]: http://marionettejs.com/
[macnemo.de]: http://macnemo.de/
[macfix.de]: http://www.macfix.de/
[testforum]: http://saito.siezi.com/forum/
