<?php

	App::uses('AppController', 'Controller');

	/**
	 * Shouts Controller
	 *
	 * @property Shout $Shout
	 */
	class ShoutsController extends AppController {

		public function index() {
			$this->autoLayout = false;
			if ($this->request->is('ajax')) {
				$shouts = $this->Shout->get();
				$this->_initBbcode();
				$this->set('shouts', $shouts);
			} else {
				throw new NotFoundException();
			}
		}

		public function add() {
			$this->autoRender = false;
			if ($this->request->is('ajax')) {
				$data = array(
					'Shout' => array(
						'text' => $this->Bbcode->prepareInput(
							$this->request->data['text']
						),
						'user_id' => $this->CurrentUser->getId()
					)
				);
				$success = $this->Shout->push($data);
				if ($success) {
					clearCache('shouts', null, '');
				}
				return $success;
			}
		}

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
