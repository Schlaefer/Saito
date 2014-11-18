<?php

	namespace App\View\Helper;

	use Cake\View\Helper;

	class AppHelper extends Helper {

		protected static $_tagId = 0;

		public function __get($name) {
			switch ($name) {
				case 'View':
					return $this->_View;
				default:
					return parent::__get($name);
			}
		}

		public static function tagId() {
			return 'id' . static::$_tagId++;
		}

		/**
		 * Returns the unix timestamp for a file
		 *
		 * @param $path as url `m/dist/theme.css
		 * @return int
		 * @throws InvalidArgumentException
		 */
		public function getAssetTimestamp($path) {
			$pathWithTimestamp = $this->assetTimestamp($path);
			// extracts integer unixtimestamp from `path/asset.ext?<unixtimestamp>
			if ($pathWithTimestamp) {
				if (preg_match('/(?<=\?)[\d]+(?=$|\?|\&)/', $pathWithTimestamp, $matches)) {
					return (int)$matches[0];
				}
			}
			throw new InvalidArgumentException("File $path not found.");
		}

	}