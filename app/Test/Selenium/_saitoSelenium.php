<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Saito_SeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase {
//	public static $setBrowser			= "*firefox";
//	public static $setBrowser			= "*googlechrome";

	public static $browsers	= array(
//			array( 'name' 		=> 'Firefox', 'browser'	=> '*firefox'),
			array( 'name' 		=> 'Google Chrome', 'browser'	=> '*googlechrome'),
		);

	public static $setBrowserUrl 	= "http://localhost/private/personal_projects/macnemo_2_github/";
	public static $setSpeed 			= 0;

	public static $userName 			= 'test';
	public static $userPassword 	= 'test';

	/**
	 * ID of the last root entry created
	 */
	protected $rootId = null;

	public function setUp () {
//		$this->setBrowser(self::$setBrowser);

		$this->setBrowserUrl(self::$setBrowserUrl);
		$this->browserUrl	= self::$setBrowserUrl;
		$this->setSpeed(self::$setSpeed);

		$this->userName 		= self::$userName;
		$this->userPassword = self::$userPassword;
		}

	protected function login($test_case = NULL) {
		if ( $test_case === NULL ) {
			$test_case = $this;
			}
		$test_case->open();
    $test_case->waitForPageToLoad();
		$test_case->assertEquals("0", $test_case->getElementHeight("modalLoginDialog"));
    $test_case->click("showLoginForm");
    $test_case->waitForPageToLoad("");
    $test_case->assertNotEquals("0", $test_case->getElementHeight("modalLoginDialog"));
		$test_case->type("tf-login-username", self::$userName);
		$test_case->type("UserPassword", self::$userPassword);
    $test_case->click("//input[@value='Login']");
    $test_case->waitForPageToLoad();
    $test_case->assertNotEquals("SaitoPersistent[AU]", $test_case->getCookie());
		}

	protected function logout($test_case = NULL) {
		if ( $test_case === NULL ) {
			$test_case = $this;
			}

    $test_case->assertTrue($test_case->isElementPresent("btn_logout"));
    $test_case->click("btn_logout");
    $test_case->waitForPageToLoad();
    $test_case->assertFalse($test_case->isElementPresent("btn_logout"));
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
		$this->_testCase->click("//a[contains(@href, 'entries/view/{$this->getId()}')]");
    $this->_testCase->waitForPageToLoad("30000");
    $this->_testCase->click("link=Delete");
		$this->_testCase->assertEquals('Thread wirklich löschen? – Diese Aktion kann nicht rückgängig gemacht werden!', $this->_testCase->getConfirmation());
    $this->_testCase->waitForPageToLoad("30000");
		}

	public function getId() {
		return $this->_rootEntry->getId();
		}

	public function openAddNewThreadForm() {
			$this->_testCase->open();

			$this->_testCase->click("//a[contains(@href, '/entries/add')]");
			$this->_testCase->waitForPageToLoad("30000");
			$this->_testCase->assertContains("New Entry", $this->_testCase->getTitle());
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
    $this->_id = $this->_testCase->getEval("var re = /[0-9]+$/i; re.exec(escape('".$currentLocation."'));");
    $this->_testCase->assertStringStartsWith("Betreff", $this->_testCase->getTitle());
		return $this;
		}
	
	public function open() {
		$this->_testCase->open();
		$this->_testCase->click("css=a[class*='link_show_thread {$this->_id}']");
    $this->_testCase->waitForPageToLoad("30000");
		$this->_testCase->assertEquals($this->_testCase->browserUrl.'entries/view/'.$this->_id, $this->_testCase->getLocation());
		return $this;
		}

	public function openAnswerForm() {
		// try to answer to posting
		$this->_testCase->click("forum_answer_" . $this->_id);
		// waitForElementPresent ajax call
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->_testCase->fail("timeout");
			try {
				if ($this->_testCase->isElementPresent("entry_reply")) break;
			} catch (Exception $e) {}
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
?>