<?php

	define('PRODUCTION', false);

// use this config file to overwrite the defaults from default_config.php
// or to make local config changes.
	$config = array();
	$config['encryptionKey'] = 'IdUFTSOnWZMnH2kddF[1wzKbDL1V[{m6MjedfTu4BqNdwTwz!SHSN8SOCcA9FP9v';
  $config['site_title'] = 'Saito - The Threaded Web Forum';
	$config['theme'] = 'saito';
	$config['plugins'] = array(
		'phileDemoPlugin' => array('active' => PRODUCTION),
		'phileParserMarkdown' => array('active' => true), // the default parser
		'phileTemplateTwig' => array('active' => true),
		// the default template engine
		'philePhpFastCache' => array('active' => PRODUCTION), // the default cache engine
		'phileSimpleFileDataPersistence' => array('active' => PRODUCTION),
		// the default data storage engine
	);

// it is important to return the $config array!
	return $config;
