<?php

	App::uses('ThreadHtmlRenderer', 'Lib/Thread/Renderer');

	App::uses('SaitoUser', 'Lib/SaitoUser');
	App::uses('ReadPostingsDummy', 'Lib/SaitoUser/ReadPostings');
	App::uses('PostingCurrentUserDecorator', 'Lib/Thread');

	class ThreadHtmlRendererTest extends PHPUnit_Framework_TestCase {

		public function testNesting() {
			$SaitoUser = $this->getMock('SaitoUser', ['getMaxAccession', 'getId', 'hasBookmarks']);
			$SaitoUser->ReadEntries = $this->getMock('ReadPostingsDummy');

			$entry = $entry1 = $entry2 = $entry3 = [
				'Entry' => [ 'id' => 1, 'tid' => 0, 'subject' => 'a', 'text' => 'b', 'time' => 0, 'last_answer' => 0, 'fixed' => false, 'nsfw' => false, 'solves' => '', 'user_id' => 1 ],
				'Category' => [ 'accession' => 0, 'description' => 'd', 'category' => 'c' ],
				'User' => ['username' => 'u']
			];

			$entry1['Entry']['subject'] = 'b';
			$entry2['Entry']['subject'] = 'c';
			$entry3['Entry']['subject'] = 'd';

			// root + 2 sublevels
			$entries = $entry;
			$entries['_children'] = [$entry1 + ['_children' => [$entry2]], $entry3];

			$entries = $this->EntryHelper->createTreeObject($entries);
			$entries = $entries->addDecorator(function ($node) use ($SaitoUser) {
				$node = new PostingCurrentUserDecorator($node);
				$node->setCurrentUser($SaitoUser);
				return $node;
			});
			$renderer = new ThreadHtmlRenderer($entries, $this->EntryHelper, ['maxThreadDepthIndent' => 9999]);
			$result = $renderer->render();

			$document = new DOMDocument;
			libxml_use_internal_errors(true);
			$document->loadHTML('<!DOCTYPE html>' . $result);
			$xpath = new DOMXPath($document);
			libxml_clear_errors();

			$this->assertEquals(2, $xpath->query('//ul[@data-id=1]/li')->length);
			$this->assertEquals(3, $xpath->query('//ul[@data-id=1]/li/ul/li')->length);
			$this->assertEquals(1, $xpath->query('//ul[@data-id=1]/li/ul/li/ul/li')->length);
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
			$LineCache->expects($this->any())->method('get');
			$View->set('LineCache', $LineCache);

			return new EntryHHelper($View);
		}

	}
