<?php

	App::uses('AppSettingModel', 'Lib/Model');

	class Setting extends AppSettingModel {

		public $name = 'Setting';

		public $primaryKey = 'name';

		public $validate = [
			'name' => [
				'rule' => ['between', 1, 255],
				'allowEmpty' => false
			],
			'value' => [
				'rule' => ['between', 0, 255],
				'allowEmpty' => true
			]
		];

		protected $_optionalEmailFields = [
			'email_contact', 'email_register', 'email_system'
		];

		/* @td getSettings vs Load why to functions? */

		/**
		 * Reads settings from DB and returns them in a compact array
		 *
		 * Note that this is the stored config in the DB. It may differ from the
		 * current config used by the app in Config::read('Saito.Settings'), e.g.
		 * when modified with a load-preset.
		 *
		 * @throws UnexpectedValueException
		 * @return array Settings
		 */
		public function getSettings() {
			$settings = $this->find('all');
			if (empty($settings)) {
				throw new UnexpectedValueException('No settings found in settings table.');
			}
			$settings = $this->_compactKeyValue($settings);

			// extract ranks
			$ranks = $this->_pipeSplitter($settings['userranks_ranks']);
			ksort($ranks);
			$settings['userranks_ranks'] = $ranks;

			// edit_delay is normed to seconds
			$this->_normToSeconds($settings, 'edit_delay');
			$this->_fillOptionalEmailAddresses($settings);

			return $settings;
		}

		/**
		 * Loads settings from storage into Configuration `Saito.Settings`
		 *
		 * @param array $preset allows to overwrite loaded values
		 * @return array Settings
		 */
		public function load($preset = []) {
			Stopwatch::start('Settings->getSettings()');

			$settings = Cache::read('Saito.appSettings');
			if (empty($settings)) {
				$settings = $this->getSettings();
				Cache::write('Saito.appSettings', $settings);
			}
			if ($preset) {
				$settings = $preset + $settings;
			}
			Configure::write('Saito.Settings', $settings);

			Stopwatch::end('Settings->getSettings()');
		}

		public function clearCache() {
			parent::clearCache();
			Cache::delete('Saito.appSettings');
		}

		/**
		 * Returns a key-value array
		 *
		 * Fast version of Set::combine($results, '{n}.Setting.name', '{n}.Setting.value');
		 *
		 * @param array $results
		 * @return array
		 */
		protected function _compactKeyValue($results) {
			$settings = array();
			foreach ($results as $result) {
				$settings[$result[$this->alias]['name']] = $result[$this->alias]['value'];
			}
			return $settings;
		}

		protected function _normToSeconds(&$settings, $field) {
			$settings[$field] = (int)$settings[$field] * 60;
		}

		/**
		 * Defaults optional email addresses to main address
		 *
		 * @param $settings
		 */
		protected function _fillOptionalEmailAddresses(&$settings) {
			foreach ($this->_optionalEmailFields as $field) {
				if (empty($settings[$field])) {
					$settings[$field] = $settings['forum_email'];
				}
			}
		}

	}