<?php

	namespace Api\Controller;

	use App\Controller\AppController;
    use Cake\Core\Configure;
    use Cake\Event\Event;
    use Saito\Api\ApiAuthException;
    use Saito\Api\ApiDisabledException;

    class ApiAppController extends AppController {

		/**
		 * @param Event $event
		 * @throws \Saito\Api\ApiDisabledException
		 * @return CakeResponse|void
		 */
		public function beforeFilter(Event $event) {
			$this->components()->unload('Security');
			parent::beforeFilter($event);

			$enabled = Configure::read('Saito.Settings.api_enabled');
			if (empty($enabled)) {
                throw new ApiDisabledException;
			}

			$allowOrigin = Configure::read('Saito.Settings.api_crossdomain');
			if (!empty($allowOrigin)) {
				$this->response->header('Access-Control-Allow-Origin', $allowOrigin);
			}

            $this->request->addDetector('json', [$this, 'isJson']);
		}

		public function isJson() {
			return $this->response->type() === 'application/json';
		}

		/**
		 * Throws Error if action is only allowed for logged in users
		 *
		 * @throws ApiAuthException
		 */
		protected function _checkLoggedIn() {
			$this->Auth->unauthorizedRedirect = false;
			if ($this->CurrentUser->isLoggedIn() === false &&
					!in_array($this->request->action, $this->Auth->allowedActions)
			) {
				throw new ApiAuthException();
			}
		}

	}
