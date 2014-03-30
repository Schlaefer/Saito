<?php

	abstract class SitemapGenerator {

		/**
		 * Number of URLs per sitemap file.
		 *
		 * Mostly bound by memory available to PHP on server
		 *
		 * @var int
		 */
		protected $_divider = 20000;

		protected $_Controller;

		protected $_type = null;

		/**
		 * @param Controller $Controller
		 * @throws Exception
		 */
		public function __construct(Controller $Controller) {
			$this->_Controller = $Controller;
			if ($this->_type === null) {
				throw new Exception('SitemapGenerator type not set.');
			}
			return $this;
		}

		/**
		 * Returns sitemap file
		 *
		 * @return array keys: 'url'
		 */
		abstract public function files();

		public function content($file) {
			list($type, $params) = $this->_parseFilename($file);
			if ($type !== $this->_type) {
				return false;
			}
			return $this->_content($this->_parseParams($params));
		}

		/**
		 *
		 * @param $name
		 * @return array
		 * @throws InvalidArgumentException
		 */
		protected function _parseFilename($name) {
			preg_match('/sitemap-(?P<type>\w*)(-(?P<params>.*))?(\.(\w*))?$/',
					$name,
					$matches);
			if (empty($matches['type'])) {
				throw new InvalidArgumentException;
			}
			return [$matches['type'], explode('-', $matches['params'])];
		}

		/**
		 * Parse and validate params. Should throw exception if params are not valid.
		 *
		 * @param $params
		 * @return mixed processed params
		 */
		abstract protected function _parseParams($params);

		/**
		 * Generate urls
		 *
		 * @param $params
		 * @return string urls
		 */
		abstract protected function _content($params);

		protected function _filename($params = []) {
			$filename = "sitemap-{$this->_type}";
			if ($params) {
				$filename .= '-' . implode('-', $params);
			}
			return $filename;
		}

	}