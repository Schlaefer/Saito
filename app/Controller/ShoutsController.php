<?php

	App::uses('AppController', 'Controller');

/**
 * Shouts Controller
 */
	class ShoutsController extends AppController {

		public $components = [
			'Shouts',
		];

/**
 * @throws NotFoundException
 */
		public function index() {
			$this->autoRender = false;
			$this->autoLayout = false;
			if ($this->request->is('ajax')) {
				$this->Shouts->setShoutsForView();
				$this->render('../Elements/shouts/shouts');
			} else {
				throw new NotFoundException();
			}
		}

/**
 * @return mixed
 */
		public function add() {
			$this->autoRender = false;
			if ($this->request->is('ajax')) {
				$data = [
					'Shout' => [
						'text' => $this->request->data['text'],
						'user_id' => $this->CurrentUser->getId()
					]
				];
				return $this->Shout->push($data);
			}
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

			$this->Shout->maxNumberOfShouts = Configure::read(
				'Saito.Settings.shoutbox_max_shouts'
			);
		}

	}
