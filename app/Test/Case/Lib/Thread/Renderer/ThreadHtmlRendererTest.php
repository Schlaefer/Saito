<?php

	App::uses('SaitoTestAssertTrait', 'Lib/Test');

	App::uses('ThreadHtmlRenderer', 'Lib/Thread/Renderer');

	App::uses('SaitoUser', 'Lib/SaitoUser');
	App::uses('ReadPostingsDummy', 'Lib/SaitoUser/ReadPostings');
	App::uses('PostingCurrentUserDecorator', 'Lib/Thread');

	class ThreadHtmlRendererTest extends PHPUnit_Framework_TestCase {

		use SaitoTestAssertTrait;

		/**
		 * tests that posting of ignored user is/not ignored
		 */
		public function testIgnore() {
			$entry = [
				'Entry' => [ 'id' => 1, 'tid' => 0, 'subject' => 'a', 'text' => 'b', 'time' => 0, 'last_answer' => 0, 'fixed' => false, 'nsfw' => false, 'solves' => '', 'user_id' => 1 ],
				'Category' => ['id' => 1, 'accession' => 0, 'description' => 'd', 'category' => 'c' ],
				'User' => ['id' => 1, 'username' => 'u']
			];

			$entries = $this->EntryHelper->createTreeObject($entry);
			$entries = $entries->addDecorator(function ($node) {
				$node = $this->getMock('PostingCurrentUserDecorator', ['isIgnored'], [$node]);
				$node->expects($this->once())->method('isIgnored')->will($this->returnValue(true));
				$node->setCurrentUser($this->SaitoUser);
				return $node;
			});

			$xPathQuery = '//ul[@data-id=1]/li[contains(@class,"ignored")]';

			//= posting should be ignored
			$options = ['maxThreadDepthIndent' => 25];
			$renderer = new ThreadHtmlRenderer($this->EntryHelper, $options);
			$result = $renderer->render($entries);
			$this->assertXPath($result, $xPathQuery);

			//= posting should not ignored with 'ignore' => false flag set
			$options['ignore'] = false;
			$renderer->setOptions($options);
			$result = $renderer->render($entries);
			$this->assertNotXPath($result, $xPathQuery);
		}

		public function testNesting() {
			$entry = $entry1 = $entry2 = $entry3 = [
				'Entry' => [ 'id' => 1, 'tid' => 0, 'subject' => 'a', 'text' => 'b', 'time' => 0, 'last_answer' => 0, 'fixed' => false, 'nsfw' => false, 'solves' => '', 'user_id' => 1 ],
				'Category' => ['id' => 1, 'accession' => 0, 'description' => 'd', 'category' => 'c' ],
				'User' => ['username' => 'u']
			];

			$entry1['Entry']['subject'] = 'b';
			$entry2['Entry']['subject'] = 'c';
			$entry3['Entry']['subject'] = 'd';

			// root + 2 sublevels
			$entries = $entry;
			$entries['_children'] = [$entry1 + ['_children' => [$entry2]], $entry3];

			$entries = $this->EntryHelper->createTreeObject($entries);
			$entries = $entries->addDecorator(function ($node) {
				$node = new PostingCurrentUserDecorator($node);
				$node->setCurrentUser($this->SaitoUser);
				return $node;
			});
			$renderer = new ThreadHtmlRenderer($this->EntryHelper, ['maxThreadDepthIndent' => 9999]);

			$result = $renderer->render($entries);

			$this->assertXPath($result, '//ul[@data-id=1]/li', 2);
			$this->assertXPath($result, '//ul[@data-id=1]/li/ul/li', 3);
			$this->assertXPath($result, '//ul[@data-id=1]/li/ul/li/ul/li');
		}

		public function testThreadMaxDepth() {
			$SaitoUser = $this->getMock(
				'SaitoUser',
				['getMaxAccession', 'getId', 'hasBookmarks']
			);
			$SaitoUser->ReadEntries = $this->getMock('ReadPostingsDummy');

			$entry = [
				'Entry' => [
					'id' => 1,
					'tid' => 0,
					'subject' => 'a',
					'text' => 'b',
					'time' => 0,
					'last_answer' => 0,
					'fixed' => false,
					'nsfw' => false,
					'solves' => '',
					'user_id' => 1
				],
				'Category' => [
					'id' => 1,
					'accession' => 0,
					'description' => 'd',
					'category' => 'c'
				],
				'User' => ['username' => 'u']
			];

			// root + 2 sublevels
			$entries = $entry;
			$entries['_children'] = [
				$entry + [
					'_children' => [
						$entry
					]
				]
			];

			$entries = $this->EntryHelper->createTreeObject($entries);
			$entries = $entries->addDecorator(function ($node) use ($SaitoUser) {
				$node = new PostingCurrentUserDecorator($node);
				$node->setCurrentUser($SaitoUser);
				return $node;
			});

			// max depth should not apply
			$renderer = new ThreadHtmlRenderer($this->EntryHelper, [
				'maxThreadDepthIndent' => 9999
			]);
			$result = $renderer->render($entries);
			$this->assertEquals(substr_count($result, '<ul'), 3);

			// max depth should only allow 1 level
			$renderer->setOptions(['maxThreadDepthIndent' => 2]);
			$result = $renderer->render($entries);
			$this->assertEquals(substr_count($result, '<ul'), 2);
		}

		public function setUp() {
			$this->EntryHelper = $this->_setupEntryHelper();

			$this->SaitoUser = new SaitoUser;
			$this->SaitoUser->ReadEntries = new ReadPostingsDummy;
		}

		protected function _setupEntryHelper() {
			App::uses('Controller', 'Controller');
			App::uses('View', 'View');
			App::uses('EntryHHelper', 'View/Helper');
			$Controller = new Controller();
			$View = new View($Controller);
			return new EntryHHelper($View);
		}

	}
