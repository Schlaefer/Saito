<?php

	App::uses('FormAuthenticate', 'Controller/Component/Auth');

	/**
   * mylittleforum 1.x auth with unsalted md5 passwords
	 */
	class MlfAuthenticate extends FormAuthenticate {

		public static function checkPassword($password, $hash) {
			return $hash === self::hash($password);
		}

    public static function hash($string) {
			return Security::hash($string, 'md5', FALSE);
    }

		protected function _password($password) {
			return self::hash($password);
		}

	}

?>