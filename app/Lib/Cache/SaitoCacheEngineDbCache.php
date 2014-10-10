<?php

	App::uses('SaitoCacheEngineInterface', 'Lib/Cache');

	class SaitoCacheEngineDbCache implements SaitoCacheEngineInterface {

		protected $_Database;

		protected function _db() {
			if ($this->_Database === null) {
				$this->_Database = ClassRegistry::init('Ecach');
				$this->_Database->primaryKey = 'key';
			}
			return $this->_Database;
		}

		public function read($name) {
			$result = $this->_db()->findByKey($name);
			if (!$result) {
				return [];
			}

			$result = @unserialize($result['Ecach']['value']);
			// catches storage overflow
			if ($result === false) {
				$this->_reset($name);
				return [];
			}

			return $result;
		}

		protected function _reset($name) {
			$this->write($name, []);
		}

		public function write($name, $content) {
			// @todo @bogus
			// Calling this write in ItemCache::__deconstruct fails in PHPUnit
			// because the table is already cleaned up and does no longer exist
			// to write the content.
			// Shouldn't never be an issue on a real server.
			try {
				return $this->_db()->save([
					'Ecach' => ['key' => $name, 'value' => serialize($content)]
				]);
			} catch (Exception $e) {
				return false;
			}
		}

	}