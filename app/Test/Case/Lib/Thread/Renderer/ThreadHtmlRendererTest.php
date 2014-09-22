<?php

	App::uses('ThreadHtmlRenderer', 'Lib/Thread/Renderer');

	class ThreadHtmlRendererTest extends PHPUnit_Framework_TestCase {

		public function testThreadMaxDepth() {
			App::uses('SaitoUser', 'Lib/SaitoUser');
			$SaitoUser = $this->getMock(
				'SaitoUser',
				['getMaxAccession', 'getId', 'hasBookmarks']
			);
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

			App::uses('ReadPostingsDummy', 'Lib/SaitoUser/ReadPostings');
			$SaitoUser->ReadEntries = $this->getMock('ReadPostingsDummy');

			$entries = $this->EntryHelper->createTreeObject($entries);
			App::uses('PostingCurrentUserDecorator', 'Lib/Thread');
			$entries = $entries->addDecorator(function ($node) use ($SaitoUser) {
				$node = new PostingCurrentUserDecorator($node);
				$node->setCurrentUser($SaitoUser);
				return $node;
			});

			// max depth should not apply
			$renderer = new ThreadHtmlRenderer($entries, $this->EntryHelper, [
				'maxThreadDepthIndent' => 9999
			]);
			$result = $renderer->render();
			$this->assertEquals(substr_count($result, '<ul'), 3);

			// max depth should only allow 1 level
			$renderer = new ThreadHtmlRenderer($entries, $this->EntryHelper, [
				'maxThreadDepthIndent' => 2
			]);
			$result = $renderer->render();
			$this->assertEquals(substr_count($result, '<ul'), 2);
		}

		public function setUp() {
			$this->EntryHelper = $this->_setupEntryHelper();
		}

		protected function _setupEntryHelper() {
			App::uses('Controller', 'Controller');
			App::uses('View', 'View');
			App::uses('EntryHHelper', 'View/Helper');
			$Controller = new Controller();
			$View = new View($Controller);

			$LineCache = $this->getMock('Object', ['get', 'set']);
			$LineCache->expects($this->any())->method('get')
				->will($this->returnValue('foo'));
			$View->set('LineCache', $LineCache);

			return new EntryHHelper($View);
		}

	}
