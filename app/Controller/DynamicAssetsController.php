<?php

	App::uses('Controller', 'Controller');

	/**
	 * Class DynamicAssets
	 *
	 * Serve dynamic assets bypassing app logic
	 *
	 * If performance becomes an issue consider writing out as static assets
	 * into webroot
	 */
	class DynamicAssetsController extends Controller {

		public $components = [];

		public $autoRender = false;

		/**
		 * Output current language strings as json
		 */
		public function langJs() {
			// dummy translation to load po files
			__d('nondynamic', 'foo');
			__d('default', 'foo');
			$domains = I18n::domains();
			$translations = $domains['nondynamic'][Configure::read('Config.language')]['LC_MESSAGES'];
			$translations += $domains['default'][Configure::read('Config.language')]['LC_MESSAGES'];
			unset($translations['%po-header']);
			$this->response->type('json');
			$this->response->cache('-1 minute', '+1 hour');
			$this->response->compress();
			return json_encode($translations);
		}

	}
