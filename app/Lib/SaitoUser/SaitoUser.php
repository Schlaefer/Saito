<?php

	App::uses('ForumsUserInterface', 'Lib/SaitoUser');
	App::uses('SaitoUserTrait', 'Lib/SaitoUser');

	class SaitoUser implements ForumsUserInterface, ArrayAccess {

		use SaitoUserTrait;

		public function __construct($settings = null) {
			if ($settings !== null) {
				$this->setSettings($settings);
			}
		}

	}
