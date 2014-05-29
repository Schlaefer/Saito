<?php

	App::uses('CacheTreeCacheEngineInterface', 'Lib/CacheTree');

	class CacheTreeDbCacheEngine implements CacheTreeCacheEngineInterface {

		protected $_Db;

		public function __construct() {
			$this->_Db = ClassRegistry::init('Ecach');
			$this->_Db->primaryKey = 'key';
		}

		public function getDeprecationSpan() {
			return 3600;
		}

		public function read() {
			$result = $this->_Db->findByKey('EntrySub');
			if (!$result) {
				return [];
			}

			$result = @unserialize($result['Ecach']['value']);
			// catches storage overflow
			if ($result === false) {
				$this->_reset();
				return [];
			}

			return $result;
		}

		protected function _reset() {
			$this->write([]);
		}

		public function write(array $data) {
			return $this->_Db->save([
				'Ecach' => ['key' => 'EntrySub', 'value' => serialize($data)]
			]);
		}

	}