<?php

	App::uses('AppController', 'Controller');

	class ApiAppController extends AppController {

		public function beforeFilter() {

			AppModel::$sanitizeEnabled = false;

			parent::beforeFilter();

			$api_enabled = Configure::read('Saito.Settings.api_enabled');
			if (empty($api_enabled)) {
				throw new \Saito\Api\ApiDisabledException;
			}

			$api_allow_origin = Configure::read('Saito.Settings.api_crossdomain');
			if (!empty($api_allow_origin)) {
				$this->response->header('Access-Control-Allow-Origin', $api_allow_origin);
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

