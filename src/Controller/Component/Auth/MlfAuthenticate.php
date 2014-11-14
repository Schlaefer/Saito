<?php

	App::uses('FormAuthenticate', 'Controller/Component/Auth');

	class MlfAuthenticate extends FormAuthenticate {

		public function __construct(ComponentCollection $collection, $settings) {
			$this->settings['passwordHasher'] = 'Mlf';
			parent::__construct($collection, $settings);
		}

	}
