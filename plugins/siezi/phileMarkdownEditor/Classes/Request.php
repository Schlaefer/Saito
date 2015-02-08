<?php

	namespace Phile\Plugin\Siezi\PhileMarkdownEditor;

	/**
	 * Class Request
	 *
	 * @author Schlaefer <openmail+sourcecode@siezi.com>
	 * @link https://github.com/Schlaefer/phileMarkdownEditor
	 * @license http://opensource.org/licenses/MIT
	 * @package Phile\Plugin\Siezi\PhileMarkdownEditor
	 */
	class Request {

		protected $_base;

		protected $_request;

		protected $_uri;

		public function __construct($request) {
			$this->_request = $request;
		}

		public function param($key) {
			if (isset($this->_request[$key])) {
				$value = $this->_request[$key];
			} else {
				$value = $this->_paramBackbone($key);
			}
			return $value;
		}

		/**
		 * Extract request param from backbone model request
		 *
		 * @param string $key
		 * @return string|null
		 */
		protected function _paramBackbone($key) {
			if (isset($this->_request['model'])) {
				$model = json_decode($this->_request['model'], true);
				return $model[$key];
			}
			return null;
		}

		public function setUri($uri) {
			$this->_uri = $uri;
		}

		public function setBase($base) {
			$this->_base = $base;
		}

		public function isEditor() {
			return !empty($this->_uri) && $this->getAction();
		}

		public function getAction() {
			$uri = rtrim($this->_uri, '/');
			$base = rtrim($this->_base, '/');

			if ($uri === $base) {
				return '/';
			}

			preg_match("/$base\/(?P<action>.*)(\/)?$/", $uri, $matches);
			if (!empty($matches['action'])) {
				return $matches['action'];
			}

			return false;
		}

	}

