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

			parent::write($message, $data);
		}

		protected function _write() {
			\CakeLog::write('saito.forbidden', $this->_message());
		}

	}

