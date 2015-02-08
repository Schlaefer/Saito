# Phile Markdown Editor Plugin #


Provides an online Markdown editor and file manager for Phile.

This is a rewrite/fork of [Pico-Editor-Plugin](https://github.com/gilbitron/Pico-Editor-Plugin) 1.1 for Phile.

[Project Home](https://github.com/Schlaefer/phileMarkdownEditor)

### 1.1 Installation (composer) ###


	php composer.phar require siezi/phile-markdown-editor:*

### 1.2 Installation (Download)

* Install [Phile](https://github.com/PhileCMS/Phile)
* Clone this repo into `plugins/siezi/phileMarkdownEditor`

### 2. Activation

After you have installed the plugin. You need to add the following line to your `config.php` file:


	$config['plugins']['siezi\\phileMarkdownEditor'] = array('active' => true);


### 3. Start ###

1. goto URL `admin/password` and create a password hash
2. put this hash into `plugins/siezi/phileMarkdownEditor/config.php`
3. goto URL `admin/` to login


