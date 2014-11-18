<?php

	namespace Saito\Test;

	use App\Test\Fixture\UserFixture;

    use Cake\Core\Configure;
    use Cake\Event\Event;
	use Cake\Event\EventManager;
	use Cake\TestSuite\IntegrationTestCase as CakeIntegrationTestCase;
	use Saito\Cache\CacheSupport;
    use Saito\Test\SecurityMockTrait;

    abstract class IntegrationTestCase extends CakeIntegrationTestCase {

        use AssertTrait;
        use SecurityMockTrait;
        use TestCaseTrait {
            getMockForTable as getMockForTableParent;
        }

		/**
		 * @var array cache environment variables
		 */
		protected $_env = [];

		public function setUp() {
			parent::setUp();
            $this->allowExceptions();
            $this->setUpSaito();
			$this->clearCaches();
		}

		public function tearDown() {
            $this->tearDownSaito();
			$this->_unsetAjax();
			$this->_unsetJson();
			$this->_unsetUserAgent();
			parent::tearDown();
			$this->clearCaches();
		}

        /**
         * throw Exceptions from integration tests
         *
         * @param bool $allow toggle exception throwing
         */
        protected function allowExceptions($allow = true) {
            if ($allow) {
                Configure::write(
                    'Error.exceptionRenderer',
                    '\Saito\Test\ExceptionRenderer'
                );
            } else {
                Configure::delete('Error.exceptionRenderer');
            }
        }

		protected function clearCaches() {
			$CacheSupport = new CacheSupport();
			$CacheSupport->clear();
			EventManager::instance()->off($CacheSupport);
			unset($CacheSupport);
		}

		protected function _setAjax() {
			$_ENV['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		}

		protected function _unsetAjax() {
			unset($_ENV['HTTP_X_REQUESTED_WITH']);
		}

		protected function _setJson() {
			$_SERVER['HTTP_ACCEPT'] = 'application/json, text/javascript';
		}

		protected function _unsetJson() {
			$_SERVER['HTTP_ACCEPT'] = "text/html,application/xhtml+xml,application/xml";
		}

		protected function _setUserAgent($agent) {
			if (isset($this->_env['HTTP_USER_AGENT'])) {
				$this->_env['HTTP_USER_AGENT'] = $_ENV['HTTP_USER_AGENT'];
			}
			$_ENV['HTTP_USER_AGENT'] = $agent;
		}

		protected function _unsetUserAgent() {
			if (isset($this->_env['HTTP_USER_AGENT'])) {
				$_ENV['HTTP_USER_AGENT'] = $this->_env('HTTP_USER_AGENT');
				unset($this->_env['HTTP_USER_AGENT']);
			} else {
				unset($_ENV['HTTP_USER_AGENT']);
			}
		}

		/**
		 * Mocks a table with methods
		 *
		 * @param $table
		 * @param array $methods
		 * @return \Cake\TestSuite\Model
		 */
		public function getMockForTable($table, array $methods = []) {
			$Mock = $this->getMockForTableParent($table, $methods);
			EventManager::instance()->on(
                'Controller.initialize',
				function (Event $event) use ($table, $Mock) {
					$Controller = $event->subject();
					$Controller->{$table} = $Mock;
				}
			);
			return $Mock;
		}

		protected function _loginUser($id) {
			// see: http://stackoverflow.com/a/10411128/1372085
			$this->_logoutUser();
			$userFixture = new UserFixture();
			$users = $userFixture->records;
            $user = $users[$id - 1];
			$this->session(['Auth.User' => $user]);
            return $user;
		}

		protected function _logoutUser() {
			// if user is logged-in it should interfere with test runs
			if (isset($_COOKIE['Saito-AU'])) :
				unset($_COOKIE['Saito-AU']);
			endif;
			if (isset($_COOKIE['Saito'])) :
				unset($_COOKIE['Saito']);
			endif;
			unset($this->_session['Auth.User']);
		}

	}
