<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Bookmarks\Test\TestCase\Model\Table;

use Saito\Test\Model\Table\SaitoTableTestCase;

/**
 * Bookmark Test Case
 */
class BookmarksTableTest extends SaitoTableTestCase
{

    public $tableClass = 'Bookmarks.Bookmarks';

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'app.UserOnline',
        'plugin.Bookmarks.Bookmark'
       ];

    /**
     * Test Bookmark table validation.
     *
     * @return void
     */
    public function testValidation()
    {
        /*
         * setup
         */
        $before = $this->Table->find()->all()->count();

        /*
         * combination user_id and entry_id not unique
         */
        $data = ['entry_id' => 1, 'user_id' => 1];
        $bookmark = $this->Table->createBookmark($data);
        $errors = $bookmark->getErrors();
        $this->assertTrue(isset($errors['entry_id']['unique']));

        /*
         * entry-ID not set
         */
        $data = ['user_id' => 1];
        $bookmark = $this->Table->createBookmark($data);
        $errors = $bookmark->getErrors();
        $this->assertTrue(isset($errors['entry_id']));

        /*
         * user-ID not set
         */
        $data = ['entry_id' => 1];
        $bookmark = $this->Table->createBookmark($data);
        $errors = $bookmark->getErrors();
        $this->assertTrue(isset($errors['user_id']));

        /*
         * posting does not exist
         */
        $data = ['entry_id' => 9999, 'user_id' => 1];
        $bookmark = $this->Table->createBookmark($data);
        $errors = $bookmark->getErrors();
        $this->assertTrue(isset($errors['entry_id']['exists']));

        /*
         * posting does not exist
         */
        $data = ['entry_id' => 1, 'user_id' => 9999];
        $bookmark = $this->Table->createBookmark($data);
        $errors = $bookmark->getErrors();
        $this->assertTrue(isset($errors['user_id']['exists']));

        /*
         * post check
         */
        $after = $this->Table->find()->all()->count();
        $this->assertEquals($before, $after);
    }
}
