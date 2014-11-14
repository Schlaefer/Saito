<?php

	App::uses('AppController', 'Controller');

	class MsController extends AppController {

		public $uses = [];

		public $helpers = [
			'RequireJs'
		];

		public function index() {
			$this->layout = 'plugin-m';
			$this->set('title_for_layout', 'Mobile');
			$this->set(
				'short_title_for_layout',
				Configure::read('Saito.Settings.forum_name')
			);
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow('index', 'manifest');

			Configure::write('Asset.timestamp', 'force');
		}

		public function manifest() {
			$this->autoLayout = false;
			$this->response->type('appcache');
			$this->response->disableCache();
		}

		public function clientLog() {
			$this->autoLayout = false;
			$this->autoRender = false;
			if (isset($this->request->data['message'])) {
				$message = $this->request->data['message'];
				echo CakeLog::error($message, 'mobile-client') ? 0 : 1;
			} else {
				echo 1;
			}
		}

	}
