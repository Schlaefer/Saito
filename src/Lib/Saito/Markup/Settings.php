<?php

	namespace Saito\Markup;

	use Cake\Core\Configure;

	class Settings {

		protected $_defaults = [
			//= default values for app settings
			'quote_symbol' => '>',
			'smilies' => false,
			//= computed values
			'atBaseUrl' => 'users/name/', // base-URL for @ tags
			'hashBaseUrl' => 'entries/view/', // base-URL for # tags
		];

		protected $_settings;

		public function __construct(array $settings) {
			$this->set($settings + $this->_defaults);
			Configure::write('Saito.Settings.Parser', $this);
			return $this;
		}

		public function add($mixed, $value = null) {
			if ($value === null) {
				$this->_settings = $mixed + $this->_settings;
			} else {
				$this->_settings[$mixed] = $value;
			}
		}

		public function get($key = null) {
			if (isset($this->_settings[$key])) {
				return $this->_settings[$key];
			}
			return $this->_settings;
		}

		public function set($settings) {
			$this->_settings = $settings;
		}

	}