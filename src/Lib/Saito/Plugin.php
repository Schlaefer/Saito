<?php

	namespace Saito;

	use Cake\Core\Configure;

	class Plugin {

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

		/**
		 * returns a convention class instance for a parser-plugin
		 *
		 * First parameter should be the name. All other arguments are passed
		 * to class constructor.
		 *
		 * The 3 convention classes required for a Parser plugin are:
		 *
		 * - <name>MarkitupSet
		 * - <name>Parser
		 * - <name>Preprocessor
		 */
		public static function getParserClassInstance() {
			$args = func_get_args();
			$name = array_shift($args);

            // @todo 3.0
			$parser = Configure::read('Saito.Settings.ParserPlugin');
			$name = "\\Plugin\\{$parser}Parser\\Lib\\$name";

			$reflection = new \ReflectionClass($name);
			return $reflection->newInstanceArgs($args);
		}

	}
