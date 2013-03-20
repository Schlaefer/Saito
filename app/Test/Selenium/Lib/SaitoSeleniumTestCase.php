<?php

	require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

	/**
	 * Setup CakePHP environment for fixtures
	 */
	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}

	if (!defined('ROOT')) {
		define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
	}

	if (!defined('APP_DIR')) {
		define('APP_DIR', basename(dirname(dirname(dirname(dirname(__FILE__))))));
	}

	if (!defined('WEBROOT_DIR')) {
		define('WEBROOT_DIR', basename(dirname(dirname(dirname(__FILE__)))));
	}
	if (!defined('WWW_ROOT')) {
		define('WWW_ROOT', dirname(dirname(dirname(__FILE__)) . DS));
	}

	require_once ROOT . DS . 'lib' . DS . 'Cake' . DS . 'bootstrap.php';
	require_once ROOT . DS . 'lib' . DS . 'Cake' . DS . 'Core' . DS . 'App.php';
	App::uses('CakeFixtureManager', 'TestSuite/Fixture');
	App::load('CakeFixtureManager');
	App::uses('CakeTestCase', 'TestSuite');
	App::load('CakeTestCase');

	class CakeTestCaseDummy extends CakeTestCase {}

	class SaitoSeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase {

		public static $browsers = array(
//			array( 'name' 		=> 'Firefox', 'browser'	=> '*firefox'),
			array('name' => 'Google Chrome', 'browser' => '*googlechrome'),
		);

		public static $setBrowserUrl = "http://localhost/private/personal_projects/macnemo_2_github/";

		/**
		 * @var int time between commands in seconds
		 */
		public static $setSleep = 0;

		public static $userName = 'Alice';
		public static $userPassword = 'test';

		/**
		 * ID of the last root entry created
		 */
		protected $rootId = null;

		/**
		 * @var array CakePHP fixtures
		 */
		public $fixtures = array(
			'app.bookmark',
			'app.ecach',
			'app.user',
			'app.user_online',
			'app.entry',
			'app.category',
			'app.smiley',
			'app.shout',
			'app.smiley_code',
			'app.setting',
			'app.upload',
			'app.esevent',
			'app.esnotification'
		);

		public function setUp() {
			$this->setBrowserUrl(self::$setBrowserUrl);
			$this->browserUrl = self::$setBrowserUrl;
			$this->setSleep(self::$setSleep);

			$this->userName = self::$userName;
			$this->userPassword = self::$userPassword;

			$this->_fixtureManager = new CakeFixtureManager();
			$this->_cakeTest = new CakeTestCaseDummy();
			$this->_cakeTest->fixtures = $this->fixtures;
			$this->_fixtureManager->fixturize($this->_cakeTest);
			$this->_fixtureManager->load($this->_cakeTest);
		}

		public function tearDown() {
			$this->_fixtureManager->shutDown();
		}

		protected function login($test_case = null) {
			if ($test_case === null) {
				$test_case = $this;
			}
			$test_case->open();
			$test_case->waitForPageToLoad();
			$test_case->assertEquals(
				"0",
				$test_case->getElementHeight("modalLoginDialog")
			);
			$test_case->click("showLoginForm");
			$test_case->waitForPageToLoad("");
			$test_case->assertNotEquals(
				"0",
				$test_case->getElementHeight("modalLoginDialog")
			);
			$test_case->type("tf-login-username", self::$userName);
			$test_case->type("UserPassword", self::$userPassword);
			$test_case->click("//input[@value='Login']");
			$test_case->waitForPageToLoad();
			$test_case->assertNotEquals(
				"SaitoPersistent[AU]",
				$test_case->getCookie()
			);
		}

		protected function logout($test_case = null) {
			if ($test_case === null) {
				$test_case = $this;
			}

			$test_case->assertTrue($test_case->isElementPresent("btn_logout"));
			$test_case->click("btn_logout");
			$test_case->waitForPageToLoad();
			$test_case->assertFalse($test_case->isElementPresent("btn_logout"));
		}

		public function waitForPageToLoad($arg = "30000") {
			parent::waitForPageToLoad($arg);
			$this->_sleep();
		}

		protected function _sleep() {
			sleep(2);
		}

	}

	class SaitoTestThread {
		protected $_rootEntry = null;
		protected $_testCase = null;

		public function __construct(PHPUnit_Extensions_SeleniumTestCase $testCase) {
			$this->_testCase = $testCase;
		}

		public function newThread() {
			// open entries/add
			$this->openAddNewThreadForm();

			$posting = new SaitoTestPosting($this->_testCase);
			$posting->fillNewForm();
			$this->_rootEntry = $posting->sendForm();
		}

		public function removeThread() {
			$this->_testCase->open();
			$this->_testCase->waitForPageToLoad("30000");
			$this->_testCase->click(
				"//a[contains(@href, 'entries/view/{$this->getId()}')]"
			);
			$this->_testCase->waitForPageToLoad("30000");
			$this->_testCase->click("link=Delete");
			$this->_testCase->assertEquals(
				'Thread wirklich löschen? – Diese Aktion kann nicht rückgängig gemacht werden!',
				$this->_testCase->getConfirmation()
			);
			$this->_testCase->waitForPageToLoad("30000");
		}

		public function getId() {
			return $this->_rootEntry->getId();
		}

		public function openAddNewThreadForm() {
			$this->_testCase->open();

			$this->_testCase->click("//a[contains(@href, '/entries/add')]");
			$this->_testCase->waitForPageToLoad("30000");
			$this->_testCase->assertContains(
				"New Entry",
				$this->_testCase->getTitle()
			);
		}

	}

	Class SaitoTestPosting {
		protected $_id = null;
		protected $_parent = null;
		protected $_testCase = null;

		public function __construct(PHPUnit_Extensions_SeleniumTestCase $testCase) {
			$this->_testCase = $testCase;
		}

		public function fillNewForm() {
			$this->_testCase->select("EntryCategory", "label=Mac");
			$this->fillAnswerForm();
		}

		public function fillAnswerForm() {
			$this->_testCase->type("EntrySubject", "Betreff");
			$this->_testCase->type("EntryText", "Textinhalt");
			return $this;
		}

		public function sendForm() {
			$this->_testCase->click("id=btn-submit");
			$this->_testCase->waitForPageToLoad("30000");

			// check if we are at new posting
			$currentLocation = $this->_testCase->getLocation();
			$this->_id = $this->_testCase->getEval(
				"var re = /[0-9]+$/i; re.exec(escape('" . $currentLocation . "'));"
			);
			$this->_testCase->assertStringStartsWith(
				"Betreff",
				$this->_testCase->getTitle()
			);
			return $this;
		}

		public function open() {
			$this->_testCase->open();
			$this->_testCase->click("css=a[class*='link_show_thread {$this->_id}']");
			$this->_testCase->waitForPageToLoad("30000");
			$this->_testCase->assertEquals(
				$this->_testCase->browserUrl . 'entries/view/' . $this->_id,
				$this->_testCase->getLocation()
			);
			return $this;
		}

		public function openAnswerForm() {
			// try to answer to posting
			$this->_testCase->click("forum_answer_" . $this->_id);
			// waitForElementPresent ajax call
			for ($second = 0; ; $second++) {
				if ($second >= 60) {
					$this->_testCase->fail("timeout");
				}
				try {
					if ($this->_testCase->isElementPresent("entry_reply")) {
						break;
					}
				} catch (Exception $e) {
				}
				sleep(1);
			}
			return $this;
		}

		public function createAnswerTo($id) {
			$this->_id = $id;
			$this->open()->openAnswerForm()->fillAnswerForm()->sendForm();
			return $this;
		}

		public function getId() {
			return $this->_id;
		}

		public function setId($id) {
			$this->_id = $id;
		}

	}
