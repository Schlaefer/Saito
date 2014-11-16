<?php

	use Saito\Cache\CacheSupportCachelet;

	class MCacheSupportCachelet extends CacheSupportCachelet {

		public function clear($id = null) {
			touch(CakePlugin::path('M') . 'webroot/touch.txt');
		}

	}