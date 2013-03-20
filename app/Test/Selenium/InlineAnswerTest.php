<?php

	require_once 'Lib/SaitoSeleniumTestCase.php';

	class InlineAnswerTest extends SaitoSeleniumTestCase {

		public $nextId = 10;

		public function testInlineAnswer() {
			$this->login();

			$id = 6;
			$this->_testSingleLine($id);

			// test inline answering
			$this->_createNewInlineAnswer($id);
			$this->_testSingleLine($this->nextId - 1);

			// test that an inline answer of an inline answer is working
			$lastInlineAnswerId = $this->nextId - 1;
			$this->_createNewInlineAnswer($lastInlineAnswerId);
			$this->_testSingleLine($this->nextId - 1);

			// test a new sibling answer
			$this->_createNewInlineAnswer($id);
			$this->_testSingleLine($this->nextId - 1);
		}

		protected function _testSingleLine($id) {
			$this->_testInlineCloseButtons($id);
			$this->_testInlineAnswerCloseButton($id);
		}

		protected function _testInlineAnswerCloseButton($id) {
			$this->_openThreadline($id);
			$this->_openAnswerForm($id);
			// click answering close button
			$this->click("css=.js-thread_line[data-id={$id}] .btn-answeringClose");
			// wait for answering form to be closed
			for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
					if (!$this->isElementPresent("css=.js-thread_line[data-id={$id}]  #entry_reply")) break;
				} catch (Exception $e) {}
				sleep(1);
			}
			// answering form is closed but posting is still visible
			$this->assertTrue($this->_isPostingVisible($id));
			$this->_closeThreadline($id);
		}

		protected function _testInlineCloseButtons($id) {
			$this->_openThreadline($id);
			// click posting close button
			$this->_closeThreadline($id);
			$this->_waitForThreadlineVisible($id);
			$this->assertFalse($this->_isPostingVisible($id));
		}

		protected function _createNewInlineAnswer($parentId) {

			$this->_openThreadline($parentId);

			// footer in posting is visible
			$this->assertTrue(
				$this->isVisible("css=.js-thread_line[data-id={$parentId}] .l-box-footer")
			);

			// opening answer form
			$this->_openAnswerForm($parentId);

			// wait for answering form to be shown
			for ($second = 0; ; $second++) {
				if ($second >= 60) {
					$this->fail("timeout");
				}
				try {
					if ($this->isVisible("css=.js-thread_line[data-id={$parentId}]  #entry_reply")
					) {
						break;
					}
				} catch (Exception $e) {
				}
				sleep(1);
			}

			// footer in posting is now hidden
			$this->assertFalse(
				$this->isVisible("css=.js-thread_line[data-id={$parentId}] .l-box-footer")
			);

			// type subject in answering field
			$this->type("css=.js-thread_line[data-id={$parentId}]  #EntrySubject", "Id: {$this->nextId}");
			// send the inline answering form
			$this->click("css=.js-thread_line[data-id={$parentId}] #btn-submit");

			$this->_waitForThreadlineVisible($parentId);

			// test that new thread line is visible
			$this->_waitForThreadlineVisible($this->nextId);
			$this->assertTrue($this->_isThreadlineVisible($this->nextId));

			$this->nextId++;
		}

		protected function _openAnswerForm($id) {
			// opening answer form
			$this->click("css=.js-thread_line[data-id={$id}] .js-btn-setAnsweringForm");
			$this->_waitForAnsweringVisible($id);
		}

		protected function _openThreadline($id) {
			// test that thread line is visible
			$this->assertTrue(
				$this->isVisible(
					"css=.js-thread_line[data-id={$id}] .js-thread_line-content"
				)
			);
			// click to open threadline
			$this->click("css=.js-thread_line[data-id={$id}] .btn_show_thread");
			$this->_waitForPostingVisible($id);
			// threadline should now be invisible
			$this->assertFalse($this->_isThreadlineVisible($id));
		}

		protected function _closeThreadline($id) {
			$this->click("css=.js-thread_line[data-id={$id}] .js-btn-strip");
		}

		protected function _waitForPostingVisible($id) {
			$this->waitForVisibleJq(".js-thread_line[data-id={$id}] .js-entry-view-core");
		}

		protected function _waitForThreadlineVisible($id) {
			$this->waitForVisibleJq(".js-thread_line[data-id={$id}] .js-thread_line-content");
		}

		protected function _waitForAnsweringVisible($id) {
			$this->waitForVisibleJq(".js-thread_line[data-id={$id}]  #entry_reply");
		}

		protected function _isThreadlineVisible($id) {
			return $this->isVisible(
				"css=.js-thread_line[data-id={$id}] .js-thread_line-content"
			);
		}

		protected function _isPostingVisible($id) {
			return $this->isVisible(
				"css=.js-thread_line[data-id={$id}] .js-entry-view-core"
			);
		}
	}
