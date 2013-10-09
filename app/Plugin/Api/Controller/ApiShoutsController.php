<?php

	App::uses('ApiAppController', 'Api.Controller');

	class ApiShoutsController extends ApiAppController {

		public $components = [
			'Shouts'
		];

		public $helpers = [
			'Shouts'
		];

		public function shoutsGet() {
			$this->Shouts->setShoutsForView();
		}

		/**
		 * @return CakeResponse|void
		 * @throws MethodNotAllowedException
		 */
		public function beforeFilter() {
			parent::beforeFilter();
			if (Configure::read('Saito.Settings.shoutbox_enabled') == false) {
				throw new MethodNotAllowedException();
			}
		}

	}