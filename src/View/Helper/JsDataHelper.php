<?php

	namespace App\View\Helper;

	use App\Controller\Component\CurrentUserComponent;
	use Cake\Core\Configure;
	use Cake\View\Helper;
	use Cake\View\View;
	use Saito\JsData;
	use Saito\User\ForumsUserInterface;

	class JsDataHelper extends AppHelper {

		public $helpers = ['Url'];

		protected $_JsData;

		public function _getJsDataInstance() {
			if (!$this->_JsData) {
				$this->_JsData = JsData::getInstance();
			}
			return $this->_JsData;
		}

		public function getAppJs(View $View, ForumsUserInterface $CurrentUser) {
			$js = $this->_getJsDataInstance()->getJs();
			$js += [
				'app' => [
					'version' => Configure::read('Saito.v'),
					'settings' => [
						'autoPageReload' => (isset($View->viewVars['autoPageReload']) ? $View->viewVars['autoPageReload'] : 0),
						'embedly_enabled' => (bool)Configure::read('Saito.Settings.embedly_enabled'),
						'editPeriod' => (int)Configure::read('Saito.Settings.edit_period'),
						'notificationIcon' => $this->Url->assetUrl(
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
					'id' => (int)$CurrentUser['id'],
					'username' => $CurrentUser['username'],
					'user_show_inline' => $CurrentUser['inline_view_on_click'] || false,
					'user_show_thread_collapsed' => $CurrentUser['user_show_thread_collapsed'] || false
				],
				'callbacks' => [
					'beforeAppInit' => [],
					'afterAppInit' => [],
					'afterViewInit' => []
				]
			];
			$out = 'var SaitoApp = ' . json_encode($js);
			$out .= '; SaitoApp.timeAppStart = new Date().getTime();';
			return $out;
		}

		public function __call($method, $params) {
			$proxy = [$this->_getJsDataInstance(), $method];
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			} else {
				return parent::__call($method, $params);
			}
		}

	}
