<?php

	require_once 'Lib/SaitoSeleniumTestCase.php';

	class ThreadCollapseTest extends SaitoSeleniumTestCase {

		public function testThreadCollapse() {
			$this->open();
			$this->waitForPageToLoad();
			$this->assertTrue($this->isVisible("css=.threadBox[data-id=1] .btn-threadCollapse"));

			$this->assertTrue($this->isVisible("css=.threadLeaf[data-id=1]"));
			$this->assertTrue($this->isVisible("css=.threadLeaf[data-id=2]"));
			$this->assertTrue($this->isVisible("css=.fa-thread-open"));

			$this->click("css=.threadBox[data-id=1] .btn-threadCollapse");
			// wait for the close animation
			sleep(1);
			$this->assertTrue($this->isVisible("css=.fa-thread-closed"));
			$this->assertTrue($this->isVisible("css=.threadLeaf[data-id=1]"));
			$this->assertFalse($this->isVisible("css=.threadLeaf[data-id=2]"));

			$this->click("css=.threadBox[data-id=1] .btn-threadCollapse");
			// wait for the close animation
			sleep(1);
			$this->assertTrue($this->isVisible("css=.fa-thread-open"));
			$this->assertTrue($this->isVisible("css=.threadLeaf[data-id=1]"));
			$this->assertTrue($this->isVisible("css=.threadLeaf[data-id=2]"));
		}

	}
