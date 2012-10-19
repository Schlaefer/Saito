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

	/* @td getSettings vs Load why to functions? */

	/**
	 * Finds all Settings and returns them in a compact array
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
	 * Finds all Settings and writes them to Configuration and Cache
	 *
	 * @param array $preset allows to overwrite loaded values
	 * @return array Settings
	 */
	public function load($preset = array()) {
		$settings = $this->getSettings();
		if ($preset) {
			$settings = array_merge($settings, $preset);
		}
		$this->_updateConfiguration($settings);
		return $settings;
	}

	public function afterSave($created) {
		parent::afterSave($created);
		$this->load();
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