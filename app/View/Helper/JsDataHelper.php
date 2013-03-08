<?php

	App::uses('AppHelper', 'View/Helper');

	class JsDataHelper extends AppHelper {


		protected $_appJs = array(
			'msg' => array()
		);


		function getAppJs(View $View) {
			$this->_appJs += array (
				'app' => array(
					'version' => Configure::read('Saito.v'),
					'timeAppStart' => 'new Date().getTime()',
					'settings' => array (
						'webroot' => $View->request->webroot,
						'embedly_enabled' => (bool)Configure::read('Saito.Settings.embedly_enabled'),
						'upload_max_number_of_uploads' => (int)Configure::read('Saito.Settings.upload_max_number_of_uploads'),
						'upload_max_img_size' => (int)Configure::read('Saito.Settings.upload_max_img_size') * 1024,
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
			return 'var SaitoApp = ' . json_encode($this->_appJs);
		}

		function addAppJsMessage($message, $type = 'info') {

			if (!is_array($message)) {
				$message = array($message);
			}

			foreach ($message as $m) {
				$this->_appJs['msg'][] = array(
					'message' => $m,
					'type' => $type
				);
			}

		}

		function getAppJsMessages() {
			return $this->_appJs['msg'];
		}

	}
