<?php

	App::import('Lib', 'CacheSupport');

	class MCacheSupportCachelet extends CacheSupportCachelet {

		public function clear($id = null) {
			touch(CakePlugin::path('M') . 'webroot/touch.txt');
		}

	}