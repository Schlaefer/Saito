<?php

	namespace Api\Controller;

	use Cake\Core\Configure;
    use Cake\Event\Event;
    use Cake\Network\Exception\BadRequestException;

    class ApiShoutsController extends ApiAppController {

		public $components = [
			'Shouts'
		];

		public $helpers = [
			'Shouts'
		];

		/**
		 * Renders all shouts
		 */
		public function shoutsGet() {
			$this->Shouts->setShoutsForView();
		}

		/**
		 * Adds a new shout
		 *
		 * @throws BadRequestException
		 */
		public function shoutsPost() {
			$this->autoLayout = false;
			if (!isset($this->request->data['text'])) {
				throw new BadRequestException('Missing text.');
			}
			$data = [
                'text' => $this->request->data['text'],
                'user_id' => $this->CurrentUser->getId()
			];
			if ($this->Shouts->push($data)) {
				$this->Shouts->setShoutsForView();
                $this->view = 'Api.shouts_get';
			} else {
				throw new BadRequestException('Tried to save entry but failed for unknown reason.');
			}
		}

		/**
		 * Called before controller action
		 *
		 * @return CakeResponse|void
		 * @throws \MethodNotAllowedException
		 */
        public function beforeFilter(Event $event) {
			parent::beforeFilter($event);
			if (Configure::read('Saito.Settings.shoutbox_enabled') == false) {
				throw new \MethodNotAllowedException();
			}
			$this->_checkLoggedIn();
		}

	}
