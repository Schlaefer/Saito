<?php

	namespace Saito\Cache;

	class SaitoCacheEngineAppCache implements SaitoCacheEngineInterface {

		public function read($key) {
			return \Cache::read($key);
		}

		public function write($key, $data) {
			return \Cache::write($key, $data);
		}

	}
