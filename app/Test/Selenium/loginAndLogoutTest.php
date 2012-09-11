<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '_saitoSelenium.php';

// require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Description of newSeleneseTest
 *
 * @author siezi
 */
class loginAndLogoutTest extends Saito_SeleniumTestCase {

	function setUp() {
		parent::setUp();

		// $this->setSleep(1);
	}

	function testLoginAndLogoutWithoutCookie() {
		$this->login($this);
		$this->logout($this);
		}

	public function testLoginAndLogoutWithCookie() {
		$this->open();
    $this->assertEquals("0", $this->getElementHeight("modalLoginDialog"));
    $this->click("showLoginForm");
    $this->waitForPageToLoad("");
    $this->assertNotEquals("0", $this->getElementHeight("modalLoginDialog"));
		$this->type("tf-login-username", $this->userName);
		$this->type("UserPassword", $this->userPassword);
    $this->click("UserRememberMe");
    $this->click("//input[@value='Login']");
    $this->waitForPageToLoad("30000");
    $this->assertTrue((bool)preg_match('/^[\s\S]*$/',$this->getCookieByName("SaitoPersistent[AU]")));
		$this->logout($this);
		$this->assertFalse($this->isCookiePresent("macnemo[AU]"));
		}
	
}