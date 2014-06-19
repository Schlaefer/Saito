<?php

	class AppSettingModel extends AppModel {

		public function afterSave($created, $options = []) {
			parent::afterSave($created, $options);
			if (isset($options['clearCache'])) {
				if ($options['clearCache'] === false) {
					return;
				}
			}
			$this->clearCache();
		}

		public function afterDelete() {
			parent::afterDelete();
			$this->clearCache();
		}

		public function clearCache() {
			$this->_dispatchEvent('Cmd.Cache.clear', ['cache' => ['Saito', 'Thread']]);
		}

	}
