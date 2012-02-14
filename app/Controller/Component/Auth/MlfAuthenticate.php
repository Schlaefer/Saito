<?php

	App::uses('FormAuthenticate', 'Controller/Component/Auth');

	/**
	 * As long as we still use old mlf md5 passwords we overwrite the Cake
	 * Form password function so we can use unsalted passwords
	 */
	class MlfAuthenticate extends FormAuthenticate {

		protected function _password($password) {
			return Security::hash($password, null,
							Configure::read('Saito.useSaltForUserPasswords'));
		}

	}

?>