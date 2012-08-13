<?php

	App::uses('CacheTreeCacheEngine', 'Lib/CacheTree');

	class CacheTreeDbCacheEngine implements CacheTreeCacheEngine {

		protected $_Db;

		public function __construct() {
			$this->_Db						 = ClassRegistry::init('Ecach');
			$this->_Db->primaryKey = 'key';
		}

		public function getDeprecationSpan() {
			return 3600;
		}

		public function read() {
			$result = $this->_Db->findByKey('EntrySub');
			if ($result) {
				return unserialize($result['Ecach']['value']);
			}
			return array();
		}

		public function write(array $data) {
			return $this->_Db->save(array(
							'Ecach' => array(
									'key'		 => 'EntrySub',
									'value'	 => serialize($data))
					));
		}

	}