<?php

	App::uses('ModelBehavior', 'Model');
	App::uses('Router', 'Routing');

	class BbcodeBehavior extends ModelBehavior {

		public function setup(Model $Model, $settings = []) {
			$this->settings = $settings + [
						'server'  => FULL_BASE_URL,
						'webroot' => Router::url('/')
					];
		}

		public function prepareBbcode(Model $Model, $string) {
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
