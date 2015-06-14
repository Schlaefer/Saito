<?php

	namespace Saito\App;

	use Cron\Lib\Cron;
    use Saito\User\Permission;

    class Registry {

		/**
		 * @var \Aura\Di\Container;
		 */
		static protected $_DIC;

		public static function initialize() {
			$dic = new \Aura\Di\Container(new \Aura\Di\Factory);
            $dic->set('Cron', new Cron());
            $dic->set('Permission', new Permission());
            $dic->set('AppStats', $dic->lazyNew('\Saito\App\Stats'));
			$dic->params['\Saito\Posting\Posting']['CurrentUser'] = $dic->lazyGet('CU');
			self::$_DIC = $dic;
			return $dic;
		}

		public static function set($key, $object) {
			self::$_DIC->set($key, $object);
		}

		public static function get($key) {
			return self::$_DIC->get($key);
		}

		public static function newInstance($key, array $params = [], array $setter = []) {
			return self::$_DIC->newInstance($key, $params, $setter);
		}

	}
