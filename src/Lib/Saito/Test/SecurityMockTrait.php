<?php

	namespace Saito\Test;

	trait SecurityMockTrait {

		public function generate($controller, $mocks = []) {
			$byPassSecurity = false;
			if (!isset($mocks['components']['Security'])) {
				$byPassSecurity = true;
				$mocks['components']['Security'] = ['_validateCsrf', '_validatePost'];
			}
			$Mock = parent::generate($controller, $mocks);
			if ($byPassSecurity) {
				$this->assertSecurityByPass($Mock);
			}
			return $Mock;
		}

		/**
		 * Assume that SecurityComponent was called
		 *
		 * @param $Controller
		 */
		public function assertSecurityBypass($Controller) {
			$Controller->Security->expects($this->any())
				->method('_validatePost')
				->will($this->returnValue(true));
			$Controller->Security->expects($this->any())
				->method('_validateCsrf')
				->will($this->returnValue(true));
		}

	}
