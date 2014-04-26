<?php

	namespace Saito\Logger;

	\App::uses('CakeLog', 'Log');

	class ExceptionLogger {

		private $__lines = [];

		/**
		 * @param null $message
		 * @param null $data
		 * - `msgs` array with additional message-lines
		 * @throws \InvalidArgumentException
		 */
		public function write($message, $data = null) {
			//# process message(s)
			$msgs = [$message];
			if (isset($data['msgs'])) {
				$msgs = array_merge($msgs, $data['msgs']);
			}
			// prepend main message in front of metadata added by subclasses
			foreach (array_reverse($msgs) as $key => $msg) {
				$this->_add($msg, $key, true);
			}

			//# add exception data
			if (isset($data['e'])) {
				/** @var $Exception \Exception */
				$Exception = $data['e'];
				unset($data['e']);
				$message = $Exception->getMessage();
				if (!empty($message)) {
					$this->_add($message);
				}
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

			if (!empty($request->data)) {
				$this->_add($this->_filterRequestData($request->data), 'Data');
			}

			$this->_write();
		}

		/**
		 * Filters request-data which should not be in server logs, esp. passwords
		 *
		 * @param $data
		 * @return array
		 */
		protected function _filterRequestData($data) {
			if (!is_array($data)) {
				return $data;
			}
			foreach ($data as $key => $datum) {
				if (is_array($datum)) {
					$data[$key] = $this->_filterRequestData($datum);
					continue;
				}

				if (stripos($key, 'password') !== false) {
					$data[$key] = '***********';
				}
			}
			return $data;
		}

		protected function _write() {
			\CakeLog::write('saito.error', $this->_message());
		}

		protected function _message() {
			$message = [];
			$i = 1;
			foreach ($this->__lines as $line) {
				$message[] = sprintf("  #%d %s", $i, $line);
				$i++;
			}
			return "\n" . implode("\n", $message);
		}

		protected function _add($val, $key = null, $prepend = false) {
			if (is_array($val)) {
				$val = print_r($val, true);
			}
			if (is_string($key)) {
				$val = "$key: $val";
			}

			if ($prepend) {
				array_unshift($this->__lines, $val);
			} else {
				$this->__lines[] = $val;
			}
		}

	}

