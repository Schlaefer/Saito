<?php

	namespace Bookmarks\Test\TestCase\Model\Table;

	use Saito\Test\Model\Table\SaitoTableTestCase;

	/**
	 * Bookmark Test Case
	 *
	 */
	class BookmarksTableTest extends SaitoTableTestCase {

		public $tableClass = 'Bookmarks.Bookmarks';

		public $fixtures = array(
			'app.category',
			'app.entry',
			'app.esevent',
			'app.esnotification',
			'app.upload',
			'app.user',
			'app.user_online',
			'plugin.bookmarks.bookmark'
		);

		public function testDummy() {
		}

	}
