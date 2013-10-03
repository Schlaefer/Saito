<?php

	App::uses('AppController', 'Controller');

	class MsController extends AppController {

		public $uses = [];

		public $helpers = [
			'RequireJs'
		];

		public function index() {
			$this->layout = 'default';
			$this->theme = 'default';
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

			$this->set(
				'touch',
					Configure::read('debug')
					. filemtime(App::pluginPath('M') . 'View/Elements/custom_html_header.ctp')
					. filemtime(App::pluginPath('M') . 'webroot/dist/js.js')
					. filemtime(
						App::pluginPath('M') . 'webroot/dist/common.css'
					)
					. filemtime(
						App::pluginPath('M') . 'webroot/dist/theme.css'
					)
			);
		}
	}
