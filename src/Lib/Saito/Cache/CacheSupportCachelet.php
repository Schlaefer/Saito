<?php

	namespace Saito\Cache;

	abstract class CacheSupportCachelet implements CacheSupportCacheletInterface {

		public function getId() {
			if (!empty($this->_title)) {
				return $this->_title;
			}
			return preg_replace(
				'/Saito\\\Cache\\\(.*)CacheSupportCachelet/', '\\1', get_class($this)
			);
		}

	}

