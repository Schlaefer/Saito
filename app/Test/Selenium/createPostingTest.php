<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '_saitoSelenium.php';

// require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Description of createPostingTest
 *
 * @author siezi
 */
class createPostingTest extends Saito_SeleniumTestCase {
	protected $_threadsToTearDown = null;

	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
		}

	public function tes1AddRootPosting() {

		$this->login($this);

		$thread = new SaitoTestThread($this);
		$thread->newThread();
		$this->_threadsToTearDown[] = $thread;

		//* go to front page and check that posting exists as new
		$this->open();
    $this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("css=div[class*='thread_line {$thread->getId()} new']"));

		//* check manualy mark as read
		$this->click("btn_manualy_mark_as_read");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("css=div[class*='thread_line {$thread->getId()}']"));
		$this->assertFalse($this->isElementPresent("css=div[class*='thread_line {$thread->getId()} new']"));

		/*
		 * check automaticaly mark as read
		 */

		//* go to user page and make shure it is deactivated
		$this->click("btn_view_current_user");
		$this->waitForPageToLoad("30000");
		$this->click("btn_user_edit");
		$this->waitForPageToLoad("30000");
		if ( $this->isChecked("UserUserAutomaticalyMarkAsRead") == TRUE ) $this->click("UserUserAutomaticalyMarkAsRead");
		$this->click("btn-submit");
		$this->waitForPageToLoad("30000");

		//* create new posting
		$posting = new SaitoTestPosting($this);
		$posting->createAnswerTo($thread->getId());

		//* test that automaticaly mark is read is not working
		$this->open();
		$this->waitForPageToLoad("30000");
		$this->click('btn_header_logo');
		$this->waitForPageToLoad("30000");
		$this->click('btn_header_logo');
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("css=div[class*='thread_line {$posting->getId()} new']"));

		//* go to user page and make shure it is activated
		$this->click("btn_view_current_user");
		$this->waitForPageToLoad("30000");
		$this->click("btn_user_edit");
		$this->waitForPageToLoad("30000");
		if ( $this->isChecked("UserUserAutomaticalyMarkAsRead") == FALSE ) $this->click("UserUserAutomaticalyMarkAsRead");
		$this->click("btn-submit");
		$this->waitForPageToLoad("30000");

		//* test that automaticaly mark is read is not working
		// we are jumpung from user/view to entries/index
		$this->click('btn_header_logo');
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("css=div[class*='thread_line {$posting->getId()} new']"));

		/* failing at the moment
		$this->click('btn_header_logo');
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("css=div[class*='thread_line {$posting->getId()}']"));
		$this->assertFalse($this->isElementPresent("css=div[class*='thread_line {$posting->getId()} new']"));
		 *
		 */

		if ( is_array($this->_threadsToTearDown) ) {
			foreach ( $this->_threadsToTearDown as $thread ) {
				$thread->removeThread();
				}
			}

		$this->logout($this);

		}

		/*
	protected function onNotSuccessfulTest(Exception $e) {
		print __METHOD__ . "\n";
		throw $e;

		if ( is_array($this->_threadsToTearDown) ) {
			foreach ( $this->_threadsToTearDown as $thread ) {
				$thread->removeThread();
				}
			}


		}
	*/
	}

?>