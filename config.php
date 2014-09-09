<?php

	define('PRODUCTION', true);

	if (!PRODUCTION) {
		error_reporting(E_ALL);
	} else {
		error_reporting(E_ERROR);
	}

// use this config file to overwrite the defaults from default_config.php
// or to make local config changes.
	$config = array();
	$config['debug']       = !PRODUCTION;
	$config['base_url']       = \Phile\Utility::getBaseUrl(); // use the Utility class to guess the base_url
	$config['pages_order'] 	= 'page.folder:desc meta.date:asc meta.title:desc';
	$config['date_format']    = 'jS M Y'; // Set the PHP date format
	$config['encryptionKey'] = 'IdUFTSOnWZMnH2kddF[1wzKbDL1V[{m6MjedfTu4BqNdwTwz!SHSN8SOCcA9FP9v';
  $config['site_title'] = 'Saito - The Threaded Web Forum';
	$config['theme'] = 'saito';

	$config['plugins'] = array(
		// key = vendor\\pluginName (vendor lowercase, pluginName lowerCamelCase
		'phile\\demoPlugin'                => array('active' => false),
		'phile\\errorHandler'              => array(
			'active' => true,
			'handler' => \Phile\Plugin\Phile\ErrorHandler\Plugin::HANDLER_DEVELOPMENT
		), // the default error handler
		'phile\\parserMarkdown'            => array('active' => true), // the default parser
		'phile\\parserMeta'                => array('active' => true), // the default parser
		'phile\\templateTwig'              => array('active' => true), // the default template engine
		'phile\\phpFastCache'              => array('active' => PRODUCTION), // the default cache engine
		'phile\\simpleFileDataPersistence' => array('active' => PRODUCTION), // the default data storage engine
		'phile\\rssFeed' 									 => array('active' => true),
		'phile\\inlineImage'							 => array('active' => true),
		'siezi\\phileMarkdownEditor'			 => array('active' => true),
	);

return $config;
