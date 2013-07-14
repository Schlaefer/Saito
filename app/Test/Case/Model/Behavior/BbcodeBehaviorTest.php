<?php
	App::uses('BbcodeBehavior', 'Model/Behavior');

	/**
	 * BbcodeBehavior Test Case
	 *
	 */
	class BbcodeBehaviorTest extends CakeTestCase {

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->Bbcode           = new BbcodeBehavior();
			$this->Bbcode->settings = [
				'server'      => 'http://example.com',
				'webroot'     => '/foo/',
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
		 * Test hashing of internal view links as string
		 *
		 * @return void
		 */
		public function tes1PrepareInputHashString() {
			$Model = $this->getMock('Model');
			$input    = 'http://example.com/foo/hash/base/345';
			$result   = $this->Bbcode->prepareBbcode($Model, $input);
			$expected = "#345";
			$this->assertEqual($result, $expected);

			$input    = '[url=http://example.com/foo/hash/base/345]foo[/url]';
			$result   = $this->Bbcode->prepareBbcode($Model, $input);
			$expected = $input;
			$this->assertEqual($result, $expected);
		}

		/**
		 * Test hashing of internal view links as array
		 *
		 * @return void
		 */
		public function testPrepareInputHashArray() {
			$Model = $this->getMock('Model');
			$input    = [
				$Model->alias => [
					'text' => 'http://example.com/foo/hash/base/345'
				]
			];
			$expected =[
				$Model->alias => [
					'text' =>  '#345'
				]
			];
			$result   = $this->Bbcode->prepareBbcode($Model, $input);
			$this->assertEqual($result, $expected);
		}

	}
