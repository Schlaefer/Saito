<?php

	namespace Saito\Test;

	use Saito;
	use Saito\User;

	class DicSetup extends Saito\DicSetup {

		public static function getNewDic(User\ForumsUserInterface $User = null) {
			$dic = parent::getNewDic();
			if ($User === null) {
				$User = new User\SaitoUser;
			}
			$dic->set('CU', $User);
			return $dic;
		}

	}