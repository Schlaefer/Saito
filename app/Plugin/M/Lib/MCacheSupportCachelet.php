<?php

	App::import('Lib', 'CacheSupport');

	class MCacheSupportCachelet extends CacheSupportCachelet {

		public function clear($id = null) {
			touch(App::pluginPath('M') . 'webroot/touch.txt');
		}

	}