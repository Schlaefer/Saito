# Markitup

This is a port of Jay Salvat's [Markitup helper] [1]

While it's great, I believed it left room for improvement. The features I have
listed below should give a clearer idea of the modifications I will be doing in
the short future.

## Added Features

* Fully configurable (done - 80%)
* Unobtrusive JS (no more Javascript::docBlock()s) (todo)
* Caching (todo)
* Include JS libraries and parser vendors (done - 50%)
* No need to create preview (controller, view) (done - 100%)
* Previews are styled as they would in any other view (done - 100%)

## Installation

	> git submodule add git://github.com/jadb/cakephp-markitup.git plugins/markitup

## Configuration

Add the `Markitup.Markitup` helper to the controller(s) where you will be using it.

Call it from the view as follow:

	$markitup->editor('field_name');

Configure the parser classes to your liking (optional):

	Configure::write('Markitup.vendors', array(
		'bbcode' => array('BBCodeParser'),
		'markdown' => array(
			'class' => 'CustomMarkdownParser',
			'file' => 'custom_markdown_parser.php
		),
	));

Configure any JS/CSS path from the view (optional):

	$markitup->paths['jQuery'] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery';

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

## Bugs & Feedback

[http://github.com/jadb/cakephp-markitup/issues] [2]

[1]: http://bakery.cakephp.org/articles/view/markitup-jquery-universal-markup-editor-helper
[2]: http://github.com/jadb/cakephp-markitup/issues