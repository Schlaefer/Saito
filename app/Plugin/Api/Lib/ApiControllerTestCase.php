<?php

	App::uses('SaitoControllerTestCase', 'Lib');

	class ApiControllerTestCase extends SaitoControllerTestCase {

		public function setUp() {
			Configure::write('Saito.Settings.api_enabled', '1');
			parent::setUp();
		}

		protected function _checkDisallowedRequestType($types, $url) {
			foreach($types as $requestType) {
				try {
					$this->testAction($url, ['method' => $requestType]);
				} catch (Exception $exception) {
					$this->assertEqual(get_class($exception), 'Saito\Api\UnknownRouteException');
				}
			}
		}

	}