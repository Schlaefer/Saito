<?php

	App::uses('CacheTreeCacheEngine', 'Lib/CacheTree');

	class CacheTreeAppCacheEngine implements CacheTreeCacheEngine {

		public function getDeprecationSpan() {
			$cacheConfig = Cache::settings();
			$depractionSpan = $cacheConfig['duration'];
			return $depractionSpan;
		}

		public function read() {
			return Cache::read('EntrySub');
		}

		public function write(array $data) {
			return Cache::write('EntrySub', $data);
		}
	}
