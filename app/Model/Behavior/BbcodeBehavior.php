<?php

	App::uses('ModelBehavior', 'Model');
	App::uses('Router', 'Routing');

	class BbcodeBehavior extends ModelBehavior {

		/** @var BbcodeSettings */
		protected $_settings;

		/**
		 * @param Model $Model
		 * @param $data string or array with [Alias]['text']
		 * @return mixed
		 */
		public function prepareBbcode(Model $Model, $data) {
			if (!$this->_settings) {
				$this->_settings = Configure::read('Saito.Settings.Bbcode');
			}

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
				{$this->_settings->get('server')}{$this->_settings->get('webroot')}{$this->_settings->get('hashBaseUrl')}
				(\d+)  # the id
				%imx",
				"#\\1",
				$string
			);

			return $string;
		}

	}
