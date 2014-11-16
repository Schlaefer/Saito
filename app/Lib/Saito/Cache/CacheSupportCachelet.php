<?php

	namespace Saito\Cache;

	abstract class CacheSupportCachelet implements CacheSupportCacheletInterface {

		public function getId() {
			if (!empty($this->_title)) {
				return $this->_title;
			}
			return str_replace('CacheSupportCachelet', '', get_class($this));
		}

	}

