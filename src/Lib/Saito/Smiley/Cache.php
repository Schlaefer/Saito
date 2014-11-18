<?php

	namespace Saito\Smiley;

		use Cake\Cache\Cache as CakeCache;
		use Cake\Controller\Controller;
		use Cake\Core\Configure;
		use Cake\ORM\TableRegistry;
		use Stopwatch\Lib\Stopwatch;

		/**
		 * Class Cache
		 *
		 * lazy loading of smiley models and caching for better performance
		 *
		 * @package Saito\Smiley
		 */
	class Cache {

		protected $_smilies = [];

		public function get() {
			if (empty($this->_smilies)) {
				Stopwatch::start('load Smilies');
				$this->_smilies = CakeCache::read('Saito.Smilies.data');
				if (!$this->_smilies) {
					$Smilies = TableRegistry::get('Smilies');
					$this->_smilies = $Smilies->load();
					CakeCache::write('Saito.Smilies.data', $this->_smilies);
				}
				Stopwatch::stop('load Smilies');
			}
			return $this->_smilies;
		}

		public function getAdditionalSmilies() {
			return Configure::read('Saito.markItUp.additionalButtons');
		}

	}

