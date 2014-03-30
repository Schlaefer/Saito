<?php

	namespace Saito;

	class ForbiddenException extends \HttpException {

		private $__Logger;

		/**
		 * @throws \InvalidArgumentException
		 */
		public function __construct($message = null, $data = []) {
			$this->__Logger = new ForbiddenExceptionLogger();
			if (empty($message)) {
				$message = 'Forbidden';
			}

			$this->__Logger->add($message);

			if (isset($data['CurrentUser'])) {
				$CurrentUser = $data['CurrentUser'];
				if (!is_a($data['CurrentUser'], 'CurrentUserComponent')) {
					throw new \InvalidArgumentException;
				}
				$username = $CurrentUser->isLoggedIn() ? $CurrentUser['username'] : 'anonymous';
				$messages[] = $this->__Logger->add($username, 'Current User');
			}

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
				$messages[] = $this->__Logger->add($url, 'Request URL');
			}

			if ($requestMethod === 'POST') {
				$messages[] = $this->__Logger->add($request->data, 'Data');
			}

			$this->__Logger->write();
			parent::__construct($message, 403);
		}

	}

	\App::uses('CakeLog', 'Log');

	class ForbiddenExceptionLogger {

		private $__lineNumber = 0;

		private $__lines = [];

		public function write() {
			\CakeLog::write('saito.forbidden', "\n" . implode("\n", $this->__lines));
		}

		public function add($val, $key = null) {
			if (is_array($val)) {
				$val = print_r($val, true);
			}
			if ($key !== null) {
				$val = "$key: $val";
			}
			$this->__lineNumber++;
			$this->__lines[] = "  #{$this->__lineNumber} $val";
		}

	}

