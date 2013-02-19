<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Helper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class AppHelper extends Helper {

	function getAssetTimestamp($path) {
			$filepath = preg_replace('/^' . preg_quote($this->request->webroot, '/') . '/', '', $path);
			$webrootPath = WWW_ROOT . str_replace('/', DS, $filepath);
			return @filemtime($webrootPath);
	}

	function getAppJs(View $View) {
		$SaitoApp = array (
			'app' => array(
				'version' => Configure::read('Saito.v'),
				'timeAppStart' => 'new Date().getTime()',
				'settings' => array (
					'webroot' => $View->request->webroot,
					'embedly_enabled' => (bool)Configure::read('Saito.Settings.embedly_enabled'),
					'upload_max_number_of_uploads' => (int)Configure::read('Saito.Settings.upload_max_number_of_uploads'),
					'upload_max_img_size' => (int)Configure::read('Saito.Settings.upload_max_img_size'),
					'autoPageReload' => (isset($View->viewVars['autoPageReload']) ? $View->viewVars['autoPageReload'] : 0)
				)
			),
			'request' => array(
				'action' => $View->request->action,
				'controller' => $View->request->controller,
				'isMobile' => $View->request->isMobile(),
				'isPreview' => $View->request->isPreview()
			),
			'currentUser' => array(
				'id' => $View->viewVars['CurrentUser']['id'],
				'username' => $View->viewVars['CurrentUser']['username'],
				'user_show_inline' => $View->viewVars['CurrentUser']['inline_view_on_click'] || false,
				'user_show_thread_collapsed' => $View->viewVars['CurrentUser']['user_show_thread_collapsed'] || false
			)
		);
		return 'var SaitoApp = ' . json_encode($SaitoApp);
	}
}