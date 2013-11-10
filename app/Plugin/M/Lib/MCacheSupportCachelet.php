<?php

	App::import('CacheSupport', 'Lib');

	class MCacheSupportCachelet extends CacheSupportCachelet {

		public function clear($id = null) {
			touch(App::pluginPath('M') . 'webroot/touch.txt');
		}

	}