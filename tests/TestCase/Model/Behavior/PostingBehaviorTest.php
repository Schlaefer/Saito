<?php

/**
 * Saito - The Threaded Web Forum
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Saito\Posting\Posting;
use Saito\Posting\PostingMarshaller;
use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

class PostingBehaviorTest extends SaitoTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'plugin.Bookmarks.Bookmark',
    ];

    /** @var PostingMarshaller */
    private $table;

    public function setUp()
    {
        parent::setUp();
        $this->table = TableRegistry::getTableLocator()->get('Entries');
    }

    public function tearDown()
    {
        unset($this->table);
        parent::tearDown();
    }

    public function testCreateUserThreadDisallowed()
    {
        $thread = ['subject' => 'foo', 'category_id' => 4];

        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $result = $this->table->createPosting($thread, $user);

        $errors = $result->getErrors();
        $this->assertArrayHasKey('isAllowed', $errors['category_id']);
    }

    public function testCreateUserAnswerDisallowed()
    {
        $answer = ['pid' => 6, 'subject' => 'foo'];
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $result = $this->table->createPosting($answer, $user);

        $errors = $result->getErrors();
        $this->assertArrayHasKey('isAllowed', $errors['category_id']);
    }

    public function testCreateUserAnswerAllowed()
    {
        $answer = ['pid' => 11, 'subject' => 'foo'];

        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $posting = $this->table->createPosting($answer, $user);

        $errors = $posting->getErrors();
        $this->assertEmpty($errors);
    }

    public function testCreateAdminAllowed()
    {
        $admin = ['id' => 101, 'username' => 'foo', 'user_type' => 'admin'];
        $admin = CurrentUserFactory::createLoggedIn($admin);

        $thread = ['subject' => 'foo', 'category_id' => 4];
        $answer = ['pid' => 11] + $thread;

        foreach ([$thread, $answer] as $data) {
            $posting = $this->table->createPosting($answer, $admin);

            $this->assertEmpty($posting->getErrors());
        }
    }

    public function testCreateParentDoesNotExist()
    {
        $answer = ['pid' => 9999, 'subject' => 'foo'];
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1564756571);

        $this->table->createPosting($answer, $user);
    }

    public function testCreateNewThreadButNoCategoryProvided()
    {
        $answer = ['subject' => 'foo'];
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $result = $this->table->createPosting($answer, $user);

        $errors = $result->getErrors();
        $this->assertArrayHasKey('_required', $errors['category_id']);
    }

    public function testUpdateSuccesModOnPinnedPosting()
    {
        $now = (string)time();
        $edit = ['subject' => $now];

        $entity = $this->table->findById(11)->first();

        $user = ['id' => 7, 'user_type' => 'mod', 'username' => 'bar'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $result = $this->table->updatePosting($entity, $edit, $user);

        $this->assertEmpty($result->getErrors());
        $this->assertEquals($now, $result->get('subject'));
    }

    public function testUpdateFailureModOnOwnPosting()
    {
        $now = (string)time();
        $edit = ['subject' => $now];

        $entity = $this->table->findById(11)->first();
        $entity->set('fixed', false);

        $user = ['id' => 7, 'user_type' => 'mod', 'username' => 'bar'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $result = $this->table->updatePosting($entity, $edit, $user);

        $errors = $result->getErrors();
        $this->assertArrayHasKey('isEditingAllowed', $errors['edited_by']);
    }

    public function testPrepareChildPosting()
    {
        $parent = [
            'id' => 123,
            'category_id' => 456,
            'subject' => 'parent subject',
            'tid' => 789,
        ];
        $parent = new Posting($parent);

        $data = $this->table->prepareChildPosting($parent, []);

        $this->assertEquals(456, $data['category_id']);
        $this->assertEquals('parent subject', $data['subject']);
        $this->assertEquals(789, $data['tid']);
    }

    public function testDeletePostingCompleteThread()
    {
        $tid = 1;

        //= test thread exists before we delete it
        $countBeforeDelete = $this->table->find()
            ->where(['tid' => $tid])
            ->count();
        $expected = 6;
        $this->assertEquals($countBeforeDelete, $expected);

        $allBookmarksBeforeDelete = $this->table->Bookmarks->find()->count();

        $result = $this->table->deletePosting($tid);
        $this->assertTrue($result);

        //= all postings in thread should be deleted
        $result = $this->table->find()->where(['tid' => $tid])->count();
        $expected = 0;
        $this->assertEquals($result, $expected);

        // delete associated bookmarks
        $allBookmarksAfterDelete = $this->table->Bookmarks->find()->count();
        $numberOfBookmarksForTheDeletedThread = 2;
        $this->assertEquals(
            $allBookmarksBeforeDelete - $numberOfBookmarksForTheDeletedThread,
            $allBookmarksAfterDelete
        );
    }

    public function testDeletePostingSubthread()
    {
        $tid = 1;

        /// test thread exists before we delete it
        $countBeforeDelete = $this->table->find()
            ->where(['tid' => $tid])
            ->count();
        $expected = 6;
        $this->assertEquals($countBeforeDelete, $expected);

        $this->table->deletePosting(2);

        $after = $this->table->find('list', [
            'where' => ['tid' => $tid],
            'keyField' => 'id',
            'valueField' => 'id',
        ])->toArray();

        $this->assertArrayHasKey(1, $after);
        $this->assertArrayHasKey(8, $after);
    }
}
