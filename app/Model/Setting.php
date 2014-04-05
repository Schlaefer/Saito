<?php

class Setting extends AppModel {

	public $name = 'Setting';

	public $primaryKey = 'name';

	public $validate = array (
		/*
		'name' => array (
				'rule' => 'VALID_NOT_EMPTY',
				'message' => 'FATAL: No variable name specified'
			),
		*/
	);

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
		$settings['edit_delay'] = (int)$settings['edit_delay'] * 60;

		return $settings;
	}

/**
 * Loads settings from storage into Configuration `Saito.Settings`
 *
 * ### Options
 *
 * - `force` Force reread of from storage
 *
 * @param array $preset allows to overwrite loaded values
 * @param array
 * @return array Settings
 */
	public function load($preset = [], $options = []) {
		Stopwatch::start('Settings->getSettings()');

		$options += [
			'force' => false
		];

		if ($options['force']) {
			$settings = $this->_load();
		} else {
			$settings = Cache::read('Saito.appSettings');
			if (empty($settings)) {
				$settings = $this->_load();
			}
		}

		if ($preset) {
			$settings = array_merge($settings, $preset);
		}

		Configure::write('Saito.Settings', $settings);
		Stopwatch::end('Settings->getSettings()');
	}

	protected function _load() {
		$settings = $this->getSettings();
		Cache::write('Saito.appSettings', $settings);
		return $settings;
	}

	public function afterSave($created, $options = array()) {
		parent::afterSave($created, $options);
		$this->load(null, ['force' => true]);
	}

	public function clearCache() {
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

}