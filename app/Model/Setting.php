<?php

class Setting extends AppModel {

	public $name = 'Setting';
	public $primaryKey = 'name';

	public $validate = array (
//			'name' => array (
//					'rule' => 'VALID_NOT_EMPTY',
//					'message' => 'FATAL: No variable name specified'
//				),
	);

	/**
	 * Are settings already loaded
	 *
	 * @var bool
	 */
	protected $_loaded = FALSE;

	/* @td getSettings vs Load why to functions? */

	/**
	 * Reads settings from DB and returns them in a compact array
	 *
	 * Note that this is the stored config in the DB. It may differ from the
	 * current config used by the app in Config::read('Saito.Settings'), e.g.
	 * when modified with a load-preset.
	 *
	 * @return array Settings
	 */
	public function getSettings() {
		$settings = $this->find('all');
		$settings = $this->_compactKeyValue($settings);

    $settings['userranks_ranks'] = $this->_pipeSplitter($settings['userranks_ranks']);

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
	public function load($preset = array(), $options = array()) {
		Stopwatch::start('Settings->getSettings()');

		$defaults = array(
				'force' => FALSE,
		);
		$options = array_merge($defaults, $options);
		extract($options);

		// preset must always update Config value
		if (!empty($preset)) {
			$force = true;
		}

		$config_settings = Configure::read('Saito.Settings');
		if (!empty($config_settings) && !$force) {
			$settings = $config_settings;
		} else {
			$settings = $this->getSettings();
			if ($preset) {
				$settings = array_merge($settings, $preset);
			}
			$this->_updateConfiguration($settings);
			$this->_loaded = TRUE;
		}

		Stopwatch::end('Settings->getSettings()');
	}

	public function afterSave($created) {
		parent::afterSave($created);
		$this->load(null, array('force' => true));
	}

	/**
	 * Updates the Configuration with App Settings
	 *
	 * Namespace `Saito.Settings.key = value`
	 *
	 * @param array $settings
	 *
	 */
	private function _updateConfiguration($settings) {
		Configure::write("Saito.Settings", $settings);
	} //end _updateConfiguration()


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
		foreach($results as $result) {
			$settings[$result[$this->alias]['name']] = $result[$this->alias]['value'];
		}
		return $settings;
	}

}