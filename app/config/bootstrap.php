<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php
 *
 * This is an application wide file to load any function that is not used within a class
 * define. You can also use this to include or require any files in your application.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * App::build(array(
 *     'plugins' => array('/full/path/to/plugins/', '/next/full/path/to/plugins/'),
 *     'models' =>  array('/full/path/to/models/', '/next/full/path/to/models/'),
 *     'views' => array('/full/path/to/views/', '/next/full/path/to/views/'),
 *     'controllers' => array('/full/path/to/controllers/', '/next/full/path/to/controllers/'),
 *     'datasources' => array('/full/path/to/datasources/', '/next/full/path/to/datasources/'),
 *     'behaviors' => array('/full/path/to/behaviors/', '/next/full/path/to/behaviors/'),
 *     'components' => array('/full/path/to/components/', '/next/full/path/to/components/'),
 *     'helpers' => array('/full/path/to/helpers/', '/next/full/path/to/helpers/'),
 *     'vendors' => array('/full/path/to/vendors/', '/next/full/path/to/vendors/'),
 *     'shells' => array('/full/path/to/shells/', '/next/full/path/to/shells/'),
 *     'locales' => array('/full/path/to/locale/', '/next/full/path/to/locale/')
 * ));
 *
 */

/**
 * As of 1.3, additional rules for the inflector are added below
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */
/*
Configure::write('Markitup.vendors', array(
		'set'			=> 'bbcode',
		'skin'		=> 'bbcode',
    'bbcode' => array('markitup.bbcode_parser.php'),
));
*/

	/**
 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call
 * Uncomment one of the lines below, as you need. make sure you read the documentation on CakePlugin to use more
 * advanced ways of loading plugins
 *
 * CakePlugin::loadAll(); // Loads all plugins at once
 * CakePlugin::load('DebugKit'); //Loads a single plugin named DebugKit
 *
 */

// CakePlugin::loadAll();
CakePlugin::load('Stopwatch');
CakePlugin::load('DebugKit');
CakePlugin::load('Markitup');
CakePlugin::load('CakephpGeshi');
CakePlugin::load('Flattr');
CakePlugin::load('SimpleCaptcha');
CakePlugin::load('Install');

/**
 * Set the theme
 */
Configure::write('Saito.theme', 'default');

/**
 * Activate Saito Cache:
 *
 * true: (default) use cache
 * false: don't use cache
 */
Configure::write('Saito.Cache.Thread', false);

/**
 * Don't use core Security.salt for user passwords
 *
 * Allows usage of md5 passwords which were not hashed with salt,
 * e.g. from different datasource than Cake (i.e. old mlf in our case)
 *
 * true: (default) use Security.salt
 * false: don't use salt
 */
Configure::write('Saito.useSaltForUserPasswords', FALSE);

/**
 * Cake doesn't handle Smiley <-> Smilies
 */
Inflector::rules('plural', array( '/^(smil)ey$/i' => '\1ies' ));
Inflector::rules('singular', array( '/^(smil)ies$/i' => '\1ey' ));

include_once 'version.php';

/**
 * Add additional buttons to editor
 * @td document in namespace
 */
Configure::write('Saito.markItUp.nextCssId', 12);
Configure::write(
		'Saito.markItUp.additionalButtons',
		array(
			'Gacker' => array(
					// image in img/markitup/<button>.png
					'button'			=> 'gacker',
					// code inserted into text
					'code' 				=> ':gacker:',
					// format replacement as image
					'type'				=> 'image',
					// replacement in output
					'replacement' => 'gacker_large.png'
				),
			'Popcorn' => array(
					'button'			=> 'popcorn', //.png
					'code' 				=> ':popcorn:',
					'type'				=> 'image',
					'replacement' => 'popcorn_large.png'
				)
			)
		);
