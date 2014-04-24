<?php

	namespace Saito;

	\App::uses('CakeLog', 'Log');

	class ForbiddenLogger {

		private $__lineNumber = 0;

		private $__lines = [];

		/**
		 * @param null $message
		 * @param null|array $data
		 * - `CurrentUser`
		 * - `msgs` array with additional message-lines
		 * @throws \InvalidArgumentException
		 */
		public function write($message = null, $data = null) {
			//# process message(s)
			if (empty($message)) {
				$message = 'Forbidden';
			}

			$msgs = [$message];
			if (isset($data['msgs'])) {
				$msgs = array_merge($msgs, $data['msgs']);
			}
			foreach ($msgs as $key => $msg) {
				$this->_add($msg, $key);
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

			//# add request data
			$request = (php_sapi_name() !== 'cli') ? \Router::getRequest() : false;

			$url = false;
			if (isset($data['URL'])) {
				$url = $data['URL'];
			} elseif ($request) {
				$url = $request->here();
			}

			$requestMethod = $request ? $request->method() : false;
			if ($url && $requestMethod) {
				$url .= ' ' . $requestMethod;
			}
			if ($url) {
				$this->_add($url, 'Request URL');
			}

			if ($requestMethod === 'POST') {
				$this->_add($request->data, 'Data');
			}

			//# output
			$message = "\n" . implode("\n", $this->__lines);
			\CakeLog::write('saito.forbidden', $message);
		}

		protected function _add($val, $key = null) {
			if (is_array($val)) {
				$val = print_r($val, true);
			}
			if (is_string($key)) {
				$val = "$key: $val";
			}
			$this->__lineNumber++;
			$this->__lines[] = "  #{$this->__lineNumber} $val";
		}

	}

