<?php

	App::uses('AppHelper', 'View/Helper');

	class RequireJsHelper extends AppHelper {

		public $helpers = array(
			'Html',
			'Js'
		);

		public $requireUrl = 'lib/require/require.min';

		/**
		 * Inserts <script> tag for including require.js
		 *
		 * ### Options
		 *
		 * - `jsUrl` Base url to javascript. 'app/webroot/js' by default.
		 * - `requireUrl Url to require.js. Without '.js' extension.
		 *
		 * @param string $dataMain data-main tag start script without .js extension
		 * @param array $options additional options
		 */
		public function scriptTag($dataMain, $options = array()) {
			$options += array(
				'jsUrl' => JS_URL,
				'requireUrl' =>  $this->requireUrl
			);
			$out = '';
			// add version as timestamp to require requests
			$isTimestampShown = Configure::read('Asset.timestamp');
			if ($isTimestampShown === 'force'
					|| ($isTimestampShown === true && Configure::read('debug') > 0)
			) {
					$out .= $this->Html->scriptBlock(
						"var require = {urlArgs:"
								. $this->Js->value($this->Html->getAssetTimestamp($options['jsUrl'] . $dataMain . '.js'))
								. "
						}");
			}
			// require.js borks out when used with Cakes timestamp.
			// also we need the relative path for the main-script
			$tmp_asset_timestamp_cache = Configure::read('Asset.timestamp');
			Configure::write('Asset.timestamp', false);
			$out .= $this->Html->script(
				$options['requireUrl'],
				array(
					'data-main' => $this->Html->assetUrl(
						$dataMain,
						array(
							'pathPrefix' => $options['jsUrl'],
							'ext'		 => '.js'

						)
					)
				)
			);
			Configure::write('Asset.timestamp', $tmp_asset_timestamp_cache);
			return $out;
		}

	}
