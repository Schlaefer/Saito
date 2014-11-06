<?php
	App::uses('SitemapsController', 'Sitemap.Controller');

	/**
	 * SitemapsController Test Case
	 *
	 */
	class SitemapsControllerTest extends ControllerTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
				'setting',
				'user',
				'user_online',
				'bookmark',
				'entry',
				'category',
				'esevent',
				'esnotification',
				'user_read',
				'upload'
		);

		/**
		 * testIndex method
		 *
		 * basic test that at least something is in the output
		 *
		 * @return void
		 */
		public function testIndex() {
			$result = $this->testAction('/sitemaps/index.xml',
					['method' => 'GET', 'return' => 'contents']);
			$baseUrl = $this->controller->base;
			$this->assertContains($baseUrl . '/sitemaps/file/sitemap-entries-1-20000.xml',
					$result);
		}

		/**
		 * testFile method
		 *
		 * basic test that at least something is in the output
		 *
		 * @return void
		 */
		public function testFile() {
			$result = $this->testAction('/sitemaps/file/sitemap-entries-1-20000.xml',
					['method' => 'GET', 'return' => 'contents']);
			$baseUrl = $this->controller->base;
			$this->assertContains("{$baseUrl}/entries/view/1</loc>", $result);
			$this->assertNotContains("{$baseUrl}/entries/view/4</loc>", $result);
			$this->assertNotContains("{$baseUrl}/entries/view/6</loc>", $result);
		}

		public function setUp() {
			Cache::clear(false, 'sitemap');
			parent::setUp();
		}

	}
