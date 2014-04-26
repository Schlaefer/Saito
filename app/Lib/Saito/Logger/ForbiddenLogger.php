<?php

	namespace Saito\Logger;

	\App::uses('Saito\Logger\ExceptionLogger', 'Lib');

	class ForbiddenLogger extends ExceptionLogger {

		/**
		 * @param null $message
		 * @param null|array $data
		 * - `CurrentUser`
		 * - `msgs` array with additional message-lines
		 * @throws \InvalidArgumentException
		 */
		public function write($message = null, $data = null) {
			if (empty($message)) {
				$message = 'Forbidden';
			}

			//# add current user data
			if (isset($data['CurrentUser'])) {
				$CurrentUser = $data['CurrentUser'];
				if (!is_a($data['CurrentUser'], 'CurrentUserComponent')) {
					throw new \InvalidArgumentException;
				}
				$username = $CurrentUser->isLoggedIn() ? $CurrentUser['username'] : 'anonymous';
				$this->_add($username, 'Current User');
			}

			parent::write($message, $data);
		}

		protected function _write() {
			\CakeLog::write('saito.forbidden', $this->_message());
		}

	}

