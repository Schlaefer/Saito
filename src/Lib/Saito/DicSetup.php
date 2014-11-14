<?php

	namespace Saito;

	class DicSetup {

		public static function getNewDic() {
			$dic = new \Aura\Di\Container(new \Aura\Di\Factory);
			$dic->params['\Saito\Posting\Posting']['CurrentUser'] = $dic->lazyGet('CU');
			return $dic;
		}

	}