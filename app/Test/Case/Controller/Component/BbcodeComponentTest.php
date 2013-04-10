<?php
	App::uses('ComponentCollection', 'Controller');
	App::uses('Component', 'Controller');
	App::uses('BbcodeComponent', 'Controller/Component');

	/**
	 * BbcodeComponent Test Case
	 *
	 */
	class BbcodeComponentTest extends CakeTestCase {

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$Collection = new ComponentCollection();
			$this->Bbcode = new BbcodeComponent($Collection);
			$this->Bbcode->server = 'http://example.com';
			$this->Bbcode->webroot = '/foo/';
			$this->Bbcode->settings = [
				'hashBaseUrl' => 'hash/base/'
			];
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->Bbcode);

			parent::tearDown();
		}

		/**
		 * testInitHelper method
		 *
		 * @return void
		 */
		public function testInitHelper() {
		}

		/**
		 * Test hashing of internal view links
		 *
		 * @return void
		 */
		public function testPrepareInputHash() {
			$input = 'http://example.com/foo/hash/base/345';
			$result = $this->Bbcode->prepareInput($input);
			$expected  = "#345";
			$this->assertEqual($result, $expected);

			$input = '[url=http://example.com/foo/hash/base/345]foo[/url]';
			$result = $this->Bbcode->prepareInput($input);
			$expected  = $input;
			$this->assertEqual($result, $expected);
		}

	}
