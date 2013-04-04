<?php

	require_once 'Lib/SaitoSeleniumTestCase.php';

	class LoginAndLogoutTest extends SaitoSeleniumTestCase {

		function testLoginAndLogoutWithoutCookie() {
			$this->login($this);
			$this->logout($this);
		}

		public function testLoginAndLogoutWithCookie() {
			$cookie_name = "SaitoPersistent[AU]";

			$this->open();
			$this->waitForPageToLoad();
			$this->click("showLoginForm");
			$this->type("tf-login-username", $this->userName);
			$this->type("UserPassword", $this->userPassword);
			$this->click("UserRememberMe");
			$this->click("//input[@value='Login']");
			$this->waitForPageToLoad("30000");
			$this->assertTrue(
				(bool)preg_match(
					'/^[\s\S]*$/',
					$this->getCookieByName($cookie_name)
				)
			);
			$this->logout($this);
			$this->assertFalse($this->isCookiePresent($cookie_name));
		}

	}