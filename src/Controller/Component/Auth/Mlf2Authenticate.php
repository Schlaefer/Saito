<?php

	App::uses('FormAuthenticate', 'Controller/Component/Auth');

	/**
	 * mylittleforum 2.x auth with salted sha1 passwords
	 */
	class Mlf2Authenticate extends FormAuthenticate {

		public function __construct(ComponentCollection $collection, $settings) {
			$this->settings['passwordHasher'] = 'Mlf2';
			parent::__construct($collection, $settings);
		}

	}
