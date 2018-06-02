<!--
Title: Saito
Template: home
-->

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

<!--
<a href="//www.jetbrains.com/phpstorm/" style="position: relative;display:block; width:230px; height:33px; border:0; margin:4em auto 0;padding:0;text-decoration:none;text-indent:0;"><span style="margin: 0;padding: 0;position: absolute;top: 10px;left:3px;font-size: 11px;cursor:pointer;  background-image:none;border:0;color: #fff;font-family: trebuchet ms,arial,sans-serif;font-weight: normal;text-align:left;">Developed with</span><img src="//www.jetbrains.com/phpstorm/documentation/phpstorm_banners/phpstorm1/phpstorm230x33_violet.gif" alt="Developed with" border="0"/></a>
-->

[CakePHP]: http://cakephp.org/
[jBBCode]: http://jbbcode.com
[Marionette]: http://marionettejs.com/
[macnemo.de]: http://macnemo.de/
[macfix.de]: http://www.macfix.de/
[testforum]: http://saito.siezi.com/forum/
