<?php

	interface CacheTreeCacheEngineInterface {

		public function getDeprecationSpan();

		public function read();

		public function write(array $data);

	}

