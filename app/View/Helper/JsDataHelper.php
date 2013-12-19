<?php

	App::uses('AppHelper', 'View/Helper');
	App::uses('JsData', 'Lib');

	class JsDataHelper extends AppHelper {

		protected $_JsData;

		public function __construct(View $view, $settings = array()) {
			$this->_JsData = JsData::getInstance();
			parent::__construct($view, $settings);
		}

		public function getAppJs(View $View) {
			$js = $this->_JsData->getJs();
			$js += [
				'app' => [
					'version' => Configure::read('Saito.v'),
					'settings' => [
						'autoPageReload' => (isset($View->viewVars['autoPageReload']) ? $View->viewVars['autoPageReload'] : 0),
						'embedly_enabled' => (bool)Configure::read('Saito.Settings.embedly_enabled'),
						'editPeriod' => (int)Configure::read('Saito.Settings.edit_period'),
						'notificationIcon' => $this->assetUrl(
									'html5-notification-icon.png',
									[
										'pathPrefix' => Configure::read('App.imageBaseUrl'),
										'fullBase' => true
									]),
						'upload_max_img_size' => (int)Configure::read('Saito.Settings.upload_max_img_size') * 1024,
						'upload_max_number_of_uploads' => (int)Configure::read('Saito.Settings.upload_max_number_of_uploads'),
						'webroot' => $View->request->webroot
					]
				],
				'request' => [
					'action' => $View->request->action,
					'controller' => $View->request->controller,
					'isMobile' => $View->request->isMobile(),
					'isPreview' => $View->request->isPreview()
				],
				'currentUser' => [
					'id' => (int)$View->viewVars['CurrentUser']['id'],
					'username' => $View->viewVars['CurrentUser']['username'],
					'user_show_inline' => $View->viewVars['CurrentUser']['inline_view_on_click'] || false,
					'user_show_thread_collapsed' => $View->viewVars['CurrentUser']['user_show_thread_collapsed'] || false
				]
			];
			$out = 'var SaitoApp = ' . json_encode($js);
			$out .= '; SaitoApp.timeAppStart = new Date().getTime();';
			return $out;
		}

		public function __call($method, $params) {
			$proxy = array($this->_JsData, $method);
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			} else {
				return parent::__call($method, $params);
			}
		}

	}
