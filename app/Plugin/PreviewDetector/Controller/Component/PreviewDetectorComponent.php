<?php

	App::uses('Component', 'Controller');

	/**
	 * Detect if a request was made by a browser preview feature
	 *
	 * Adds the isPreview() method to the controllers request object.
	 *
	 * Supports:
	 *
	 * - Safari top sites
	 * - Firefox prefetch
	 *
	 * @copyright     Copyright 2012, Saito (http://saito.siezi.org/)
	 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
	 * @package 			Saito.Lib
	 */
	class PreviewDetectorComponent extends Component {

		public function initialize(Controller $Controller) {
			$Controller->request->addDetector(
					'preview',
					array(
							'callback' => array('PreviewDetectorComponent', 'isPreview')
					)
			);
		}

		public static function isPreview() {
			$is_preview = (
					(env('HTTP_X_PURPOSE') === 'preview') // Safari
					|| (env('HTTP_X_MOZ') === 'prefetch') // Firefox
			);
			return $is_preview;
		}

	}