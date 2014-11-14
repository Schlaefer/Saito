<?php

	namespace Saito\User;

	class SaitoUser implements ForumsUserInterface, \ArrayAccess {

		use SaitoUserTrait;

		public function __construct($settings = null) {
			if ($settings !== null) {
				$this->setSettings($settings);
			}
		}

	}
