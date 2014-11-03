<?php

	namespace Saito\Smiley;

	/**
	 * Class Cache
	 *
	 * lazy loading of smiley models and caching for better performance
	 *
	 * @package Saito\Smiley
	 */
	class Cache {

		protected $_smilies = [];

		protected $_Controller;

		public function __construct(\Controller $Controller) {
			$this->_Controller = $Controller;
		}

		public function get() {
			if (empty($this->_smilies)) {
				\Stopwatch::start('load Smilies');
				$this->_smilies = \Cache::read('Saito.Smilies.data');
				if (!$this->_smilies) {
					$this->_Controller->loadModel('Smiley');
					$this->_smilies = $this->_Controller->Smiley->load();
					\Cache::write('Saito.Smilies.data', $this->_smilies);
				}
				\Stopwatch::stop('load Smilies');
			}
			return $this->_smilies;
		}

		public function getAdditionalSmilies() {
			return \Configure::read('Saito.markItUp.additionalButtons');
		}

	}

