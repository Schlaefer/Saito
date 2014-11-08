<?php

	define('PRODUCTION', false);
	if (!PRODUCTION) {
		error_reporting(E_ALL);
	} else {
		error_reporting(E_ERROR);
	}

	$config = array();
	$config['debug'] = !PRODUCTION;
	$config['base_url'] = \Phile\Utility::getBaseUrl();
	$config['pages_order'] = 'page.folder:desc meta.date:asc meta.title:desc';
	$config['date_format'] = 'jS M Y'; // Set the PHP date format
	$config['encryptionKey'] = 'IdUFTSOnWZMnH2kddF[1wzKbDL1V[{m6MjedfTu4BqNdwTwz!SHSN8SOCcA9FP9v';
	$config['site_title'] = 'Saito - The Threaded Web Internet Forum for PHP';
	$config['theme'] = 'saito';

	$config['plugins'] = array(
		'phile\\demoPlugin' => array('active' => false),
		'phile\\errorHandler' => array(
			'active' => !PRODUCTION,
			'handler' => \Phile\Plugin\Phile\ErrorHandler\Plugin::HANDLER_DEVELOPMENT
		),
		// the default parser
		'phile\\parserMarkdown' => array('active' => true),
		// the default parser
		'phile\\parserMeta' => array('active' => true),
		'phile\\templateTwig' => array('active' => true),
		// the default template engine
		'phile\\phpFastCache' => array('active' => PRODUCTION),
		// the default cache engine
		'phile\\simpleFileDataPersistence' => array('active' => PRODUCTION),
		// the default data storage engine
		'phile\\rssFeed' => array('active' => true),
		'phile\\inlineImage' => array('active' => true),
		'siezi\\phileMarkdownEditor' => array('active' => true),
	);

	return $config;
