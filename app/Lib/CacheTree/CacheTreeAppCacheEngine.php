<?php

	App::uses('CacheTreeCacheEngineInterface', 'Lib/CacheTree');

	class CacheTreeAppCacheEngine implements CacheTreeCacheEngineInterface {

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
