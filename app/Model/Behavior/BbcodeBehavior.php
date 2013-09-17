<?php

	App::uses('BbcodeSettings', 'Lib/Bbcode');
	App::uses('ModelBehavior', 'Model');
	App::uses('Router', 'Routing');

	class BbcodeBehavior extends ModelBehavior {

		public $settings;

		public function setup(Model $Model, $settings = []) {
			$this->settings = BbcodeSettings::getInstance();
		}

		/**
		 * @param Model $Model
		 * @param $data string or array with [Alias]['text']
		 * @return mixed
		 */
		public function prepareBbcode(Model $Model, $data) {
			if (empty($data[$Model->alias]['text']) === false) {
				$data[$Model->alias]['text'] = $this->_prepareBbcode(
					$data[$Model->alias]['text']
				);
			} elseif (is_string($data)) {
				$data = $this->_hashInternalEntryLinks($data);
			}

			return $data;
		}

		protected function _prepareBbcode($string) {
			$string = $this->_hashInternalEntryLinks($string);

			return $string;
		}

		protected function _hashInternalEntryLinks($string) {
			$string = preg_replace(
				"%
				(?<!=) # don't hash if part of [url=…
				{$this->settings['server']}{$this->settings['webroot']}{$this->settings['hashBaseUrl']}
				(\d+)  # the id
				%imx",
				"#\\1",
				$string
			);

			return $string;
		}

	}
