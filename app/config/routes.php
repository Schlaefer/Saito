<?php

/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
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
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.ctp)...
 */

/**
 * installer route
 */
if ( !file_exists(APP . 'Config' . DS . 'installed.txt') ) :
	Router::connect('/', array( 'plugin' => 'install', 'controller' => 'install', 'action' => 'index' ));
else :
	Router::connect('/', array( 'controller' => 'entries', 'action' => 'index', 'home' ));
endif;

/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
Router::connect('/pages/*', array( 'controller' => 'pages', 'action' => 'display' ));

/**
 * Admin Route
 */
Router::connect('/admin', array('controller' => 'admins', 'action' => 'index', 'admin' => true));

/**
 * Mobile Route
 */
Router::connect('/mobile/:controller/:action/*', array('mobile' => true, 'prefix' => 'mobile'));


/**
 * Pagination for entries/index
 */
Router::connect('/entries/index/:page/*', array('controller' => 'entries', 'action' => 'index'), array( 'pass' => array('page'), 'page' => '[0-9]+' ));

/**
 * XML
 */
Router::mapResources('entries');
Router::parseExtensions('xml');

/**
 * RSS feed setup
 */
Router::connect('/entries/index.rss/*', array( 'controller' => 'entries', 'action' => 'feed' ));
?>