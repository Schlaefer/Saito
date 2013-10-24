<?php

	App::uses('AppController', 'Controller');

	class ApiAppController extends AppController {

/**
 * @return CakeResponse|void
 * @throws Saito\Api\ApiDisabledException
 */
		public function beforeFilter() {
			AppModel::$sanitizeEnabled = false;

			parent::beforeFilter();

			$_apiEnabled = Configure::read('Saito.Settings.api_enabled');
			if (empty($_apiEnabled)) {
				throw new \Saito\Api\ApiDisabledException;
			}

			$_apiAllowOrigin = Configure::read('Saito.Settings.api_crossdomain');
			if (!empty($_apiAllowOrigin)) {
				$this->response->header('Access-Control-Allow-Origin', $_apiAllowOrigin);
			}

			$this->request->addDetector(
				'json',
				[
					'callback' => [$this, 'isJson']
				]
			);
		}

		public function isJson() {
			return $this->response->type() === 'application/json';
		}

	}

