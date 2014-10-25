<?php

	class SaitoPlugin {

		/**
		 * loads defaults from plugin config and merges them with global config
		 *
		 * allows to override plugin/Config from app/Config
		 *
		 * @param $plugin
		 * @return array|mixed
		 */
		public static function loadConfig($plugin) {
			$global = Configure::read($plugin);
			Configure::load("$plugin.config", 'default', false);
			$settings = Configure::read($plugin);
			if (is_array($global)) {
				$settings = $global + $settings;
			}
			Configure::write($plugin, $settings);
			return $settings;
		}

	}