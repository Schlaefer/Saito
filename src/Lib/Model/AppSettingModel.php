<?php

	class AppSettingModel extends AppModel {

		/**
		 * afterSave callback
		 *
		 * @param bool $created
		 * @param array $options
		 * 	- 'clearCache' set to 'false' to prevent cache clearing
		 */
		public function afterSave($created, $options = []) {
			parent::afterSave($created, $options);
			if (!isset($options['clearCache']) || $options['clearCache'] !== false) {
				$this->clearCache();
			}
		}

		public function afterDelete() {
			parent::afterDelete();
			$this->clearCache();
		}

		public function clearCache() {
			$this->_dispatchEvent('Cmd.Cache.clear', ['cache' => ['Saito', 'Thread']]);
		}

	}
