<?php

namespace App\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Saito\App\Registry;
use Saito\Test\Model\Table\EntriesTableMock;
use Saito\Test\Model\Table\SaitoTableTestCase;
use Saito\User\Categories;
use Saito\User\SaitoUser;

class EntriesTest extends SaitoTableTestCase
{

    public $tableClass = 'Entries';

    public $fixtures = [
        'app.user',
        'app.user_online',
        'app.user_read',
        'app.entry',
        'app.category',
        'app.smiley',
        'app.smiley_code',
        'app.setting',
        'app.upload',
        'app.esevent',
        'app.esnotification',
        'plugin.bookmarks.bookmark'
    ];

    public function testCreateSuccessNewThread()
    {
        $category = 1;

        $this->Table = $this->getMockForModel(
            'Entries',
            ['_dispatchEvent'],
            ['className' => 'Saito\Test\Model\Table\EntriesTableMock']
        );

        $this->Table->expects($this->once())
            ->method('_dispatchEvent')
            ->with('Model.Thread.create', $this->anything());

        //= Setup CurrentUser
        $SaitoUser = new SaitoUser(
            ['id' => 100, 'username' => 'foo', 'user_type' => 'admin']
        );
        Registry::set('CU', $SaitoUser);

        // +1 because str_pad calculates non ascii chars to a string length of 2
        $subject = str_pad(
            'Sübject',
            $this->Table->setting('subject_maxlength') + 1,
            '.'
        );
        $data = [
            'pid' => 0,
            'subject' => $subject,
            'text' => 'Täxt',
            'category_id' => $category,
        ];

        /*
         * test success
         */
        $expectedThreadId = $this->Table->find()->count() + 1;
        $result = $this->Table->createPosting($data)->toArray();
        $expected = $data;
        $expected['tid'] = $expectedThreadId;
        $result = array_intersect_key($result, $expected);
        $this->assertEquals($result, $expected);
    }

    public function testCreateAllowanceAnswer()
    {
        /*
         * setup
         */
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $admin = ['id' => 101, 'username' => 'foo', 'user_type' => 'admin'];

        $thread = ['subject' => 'foo', 'category_id' => 4];
        $answer = ['pid' => 11] + $thread;
        $SaitoUser = new SaitoUser();
        $SaitoUser->Categories = new Categories($SaitoUser);
        Registry::set('CU', $SaitoUser);

        /*
         * user thread denied
         */
        $SaitoUser->setSettings($user);
        $new = $this->Table->createPosting($thread);
        $this->assertNotEmpty($new->getErrors());

        /*
         * admin thread allowed
         */
        $SaitoUser->setSettings($admin);
        $new = $this->Table->createPosting($thread);
        $this->assertEmpty($new->getErrors());

        /*
         * user answer allowed
         */
        $new = $this->Table->createPosting($answer);
        $this->assertEmpty($new->getErrors());

        /*
         * admin answer allowed
         */
        $SaitoUser->setSettings($admin);
        $new = $this->Table->createPosting($answer);
        $this->assertEmpty($new->getErrors());
    }

    public function testCreateAllowanceThread()
    {
        /*
         * setup
         */
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $admin = ['id' => 101, 'username' => 'foo', 'user_type' => 'admin'];

        $thread = ['subject' => 'foo', 'category_id' => 1];
        $answer = ['pid' => 6] + $thread;
        $SaitoUser = new SaitoUser();
        $SaitoUser->Categories = new Categories($SaitoUser);
        Registry::set('CU', $SaitoUser);

        /*
         * user thread denied
         */
        $SaitoUser->setSettings($user);
        $new = $this->Table->createPosting($thread);
        $this->assertNotEmpty($new->getErrors());

        /*
         * admin thread allowed
         */
        $SaitoUser->setSettings($admin);
        $new = $this->Table->createPosting($thread);
        $this->assertEmpty($new->getErrors());

        /*
         * user answer denied
         */
        $SaitoUser->setSettings($user);
        $new = $this->Table->createPosting($answer);
        $this->assertNotEmpty($new->getErrors());

        /*
         * admin answer allowed
         */
        $SaitoUser->setSettings($admin);
        $new = $this->Table->createPosting($answer);
        $this->assertEmpty($new->getErrors());
    }

    public function testToggle()
    {
        //= test that thread is unlocked
        $posting = $this->Table->get(1);
        $this->assertFalse($posting->isLocked());

        //= lock thread
        $this->Table->toggle(1, 'locked');
        $posting = $this->Table->get(1);
        $this->assertTrue($posting->isLocked());
        $posting = $this->Table->get(2);
        $this->assertTrue($posting->isLocked());

        //= unlock thread again
        $this->Table->toggle(1, 'locked');
        $posting = $this->Table->get(1);
        $this->assertFalse($posting->isLocked());
        $posting = $this->Table->get(2);
        $this->assertFalse($posting->isLocked());
    }

    /**
     * Test merge
     *
     * Merge thread 2 (root-id: 4) onto entry 2 in thread 1
     */
    public function testThreadMerge()
    {
        //= CurrentUser setup
        $SaitoUser = new SaitoUser();
        Registry::set('CU', $SaitoUser);

        // notifications must be merged
        /* @td 3.0 Notif
         * $this->Table->Esevent = $this->getMock(
         * 'Esevent', array('transferSubjectForEventType'), array(null, 'esevents', 'test'));
         * $this->Table->Esevent->expects($this->once())
         * ->method('transferSubjectForEventType')
         * ->with(4, 1, 'thread');
         */

        // entry is not appended yet
        $appendedEntry = $this->Table->find()
            ->where(['id' => 4, 'pid' => 2])
            ->count();
        $this->assertEquals($appendedEntry, 0);

        // count both threads
        $targetEntryCount = $this->Table->find()->where(['tid' => 1])->count();
        $sourceEntryCount = $this->Table->find()->where(['tid' => 4])->count();

        // do the merge
        $this->Table->threadMerge(4, 2);

        // target thread is contains now all entries
        $targetEntryCountAfterMerge = $this->Table->find()
            ->where(['tid' => 1])
            ->count();
        $this->assertEquals(
            $targetEntryCountAfterMerge,
            $sourceEntryCount + $targetEntryCount
        );

        //appended entries have category of target thread
        $targetCategoryCount = $this->Table->find()
            ->where(['tid' => 1, 'category_id' => 2])
            ->count();
        $this->assertEquals(
            $targetCategoryCount,
            $targetEntryCount + $sourceEntryCount
        );

        // source thread is gone
        $sourceEntryCountAfterMerge = $this->Table->find()
            ->where(['tid' => 4])
            ->count();
        $this->assertEquals($sourceEntryCountAfterMerge, 0);

        // posting is appended now
        $appendedEntry = $this->Table->find()
            ->where(['id' => 4, 'pid' => 2])
            ->count();
        $this->assertEquals($appendedEntry, 1);
    }

    /**
     * test that a unpinned source thread is pinned after merge if target is
     * pinned
     */
    public function testThreadMergePin()
    {
        //= CurrentUser setup
        $SaitoUser = new SaitoUser();
        Registry::set('CU', $SaitoUser);

        //= unlock source the fixture thread
        $this->Table->id = 4;
        $this->Table->toggle(4, 'locked');
        $posting = $this->Table->get(4);
        $this->assertFalse($posting->isLocked());

        //= lock the target fixture thread
        $this->Table->toggle(2, 'locked');
        $posting = $this->Table->get(2);
        $this->assertTrue($posting->isLocked());

        //= merge
        $this->Table->threadMerge(4, 2);

        //= test
        $posting = $this->Table->get(4);
        $this->assertTrue($posting->isLocked());
    }

    /**
     * test that a pinned source thread is unpinned before merge
     */
    public function testThreadMergeUnpin()
    {
        //= CurrentUser setup
        $SaitoUser = new SaitoUser();
        Registry::set('CU', $SaitoUser);

        $posting = $this->Table->get(4);
        $this->assertTrue($posting->isLocked());

        $success = $this->Table->threadMerge(4, 2);
        $this->assertTrue($success);

        $posting = $this->Table->get(4);
        $this->assertFalse($posting->isRoot());
        $this->assertEquals(1, $posting->get('tid'));
    }

    /**
     * Merge subposting 5 in thread 2 onto root-posting in thread 1
     */
    public function testThreadMergeSourceIsNoThreadRoot()
    {
        $result = $this->Table->threadMerge(5, 1);
        $this->assertFalse($result);
    }

    public function testThreadMergeThreadOntoItself()
    {
        $result = $this->Table->threadMerge(2, 1);
        $this->assertFalse($result);
    }

    /**
     * Test changing the category of a thread
     *
     * - Should change category-ID of every posting
     * - Should update the counter-cache for threads in category
     */
    public function testChangeThreadCategory()
    {
        $SaitoUser = new SaitoUser(['id' => 1, 'user_type' => 'admin']);
        Registry::set('CU', $SaitoUser);

        $tid = 1;
        $oldCategory = 2;
        $newCategory = 1;

        $nPostingsBefore = $this->Table->find()
            ->where(['tid' => $tid, 'category_id' => $oldCategory])
            ->count();
        // there should be postings in that thread we move
        $this->assertGreaterThan(1, $nPostingsBefore);

        $nThreadsOldCategoryBefore = $this->Table->find()
            ->where(['pid' => 0, 'category_id' => $oldCategory])
            ->count();
        $categoryOld = $this->Table->Categories->find()
            ->where(['id' => $oldCategory])
            ->first();
        // check that thread counter cache is in order for old category
        $this->assertEquals($categoryOld->get('thread_count'), $nThreadsOldCategoryBefore);

        $nThreadsNewCategoryBefore = $this->Table->find()
            ->where(['pid' => 0, 'category_id' => $newCategory])
            ->count();
        $categoryNew = $this->Table->Categories->find()
            ->where(['id' => $newCategory])
            ->first();
        // check that thread counter cache is in order for new category
        $this->assertEquals($categoryNew->get('thread_count'), $nThreadsNewCategoryBefore);

        $posting = $this->Table->get(1, ['return' => 'Entity']);
        $this->Table->patchEntity($posting, ['category_id' => $newCategory]);
        $this->Table->save($posting);

        $nThreadsOldCategoryAfter = $this->Table->find()
            ->where(['pid' => 0, 'category_id' => $oldCategory])
            ->count();
        // thread should be removed from old category
        $this->assertEquals(--$nThreadsOldCategoryBefore, $nThreadsOldCategoryAfter);

        $categoryOld = $this->Table->Categories->find()
            ->where(['id' => $oldCategory])
            ->first();
        // check that thread counter cache is in order for old category
        $this->assertEquals($categoryOld->get('thread_count'), $nThreadsOldCategoryAfter);

        $nThreadsNewCategoryAfter = $this->Table->find()
            ->where(['pid' => 0, 'category_id' => $newCategory])
            ->count();
        // thread should be added to new category
        $this->assertEquals(++$nThreadsNewCategoryBefore, $nThreadsNewCategoryAfter);

        $categoryNew = $this->Table->Categories->find()
            ->where(['id' => $newCategory])
            ->first();
        // check that thread counter cache is in order for old category
        $this->assertEquals($categoryNew->get('thread_count'), $nThreadsNewCategoryAfter);

        $nPostingsAfter = $this->Table->find()
            ->where(['tid' => $tid, 'category_id' => $newCategory])
            ->count();
        // check category was changed on all postings
        $this->assertEquals($nPostingsBefore, $nPostingsAfter);
    }

    public function testChangeThreadCategoryNotAnExistingCategory()
    {
        $SaitoUser = new SaitoUser(['id' => 1, 'user_type' => 'admin']);
        Registry::set('CU', $SaitoUser);

        $newCategory = 9999;

        $posting = $this->Table->get(1, ['return' => 'Entity']);
        $this->Table->patchEntity($posting, ['category_id' => $newCategory]);
        $result = $this->Table->save($posting);
        $this->assertFalse($result);
    }

    public function testDeleteNodeCompleteThread()
    {
        $tid = 1;

        //= test thread exists before we delete it
        $countBeforeDelete = $this->Table->find()
            ->where(['tid' => $tid])
            ->count();
        $expected = 6;
        $this->assertEquals($countBeforeDelete, $expected);

        // test that event and notifications are deleted
        // @td 3.0 Notif
        /*
        $this->Table->Esevents = $this->getMockForModel(
            'Esevents', ['deleteSubject']
        );

        // delete thread
        $this->Table->Esevents->expects($this->at(0))
                ->method('deleteSubject')
                ->with($tid, 'thread');
        // delete first entry
        $this->Table->Esevents->expects($this->at(1))
                ->method('deleteSubject')
                ->with($tid, 'entry');
        // delete sum: 1 thread + all entries in thread
        $_deletedSubjects = 1 + $this->Table->findAllByTid($tid)->count();
        $this->Table->Esevents->expects($this->exactly($_deletedSubjects))
                ->method('deleteSubject');
        */

        $allBookmarksBeforeDelete = $this->Table->Bookmarks->find()->count();

        $result = $this->Table->treeDeleteNode($tid);
        $this->assertTrue($result);

        //= all postings in thread should be deleted
        $result = $this->Table->find()->where(['tid' => $tid])->count();
        $expected = 0;
        $this->assertEquals($result, $expected);

        // delete associated bookmarks
        $allBookmarksAfterDelete = $this->Table->Bookmarks->find()->count();
        $numberOfBookmarksForTheDeletedThread = 2;
        $this->assertEquals(
            $allBookmarksBeforeDelete - $numberOfBookmarksForTheDeletedThread,
            $allBookmarksAfterDelete
        );
    }

    public function testAnonymizeEntriesFromUser()
    {
        $this->Table->anonymizeEntriesFromUser(3);

        $entriesBeforeActions = $this->Table->find()->count();

        // user has no entries anymore
        $expected = 0;
        $result = $this->Table->find()->where(['user_id' => 3])->count();
        $this->assertEquals($result, $expected);

        // entries are now assigned to user_id 0
        $expected = 7;
        $result = $this->Table->find()->where(['user_id' => 0])->count();
        $this->assertEquals($result, $expected);

        // name is removed
        $expected = 0;
        $result = $this->Table->find()->where(['name' => 'Ulysses'])->count();
        $this->assertEquals($result, $expected);

        // edited by is removed
        $expected = 0;
        $result = $this->Table->find()
            ->where(['edited_by' => 'Ulysses'])
            ->count();
        $this->assertEquals($result, $expected);

        // ip is removed
        $expected = 0;
        $result = $this->Table->find()->where(['ip' => '1.1.1.1'])->count();
        $this->assertEquals($result, $expected);

        // all entries are still there
        $expected = $entriesBeforeActions;
        $result = $this->Table->find()->count();
        $this->assertEquals($result, $expected);
    }

    public function testIsRoot()
    {
        $isRoot = new \ReflectionMethod($this->Table, '_isRoot');
        $isRoot->setAccessible(true);

        $result = $isRoot->invoke($this->Table, ['id' => 8]);
        $this->assertFalse($result);

        $result = $isRoot->invoke($this->Table, ['id' => 4]);
        $this->assertTrue($result);

        $posting = ['pid' => 0];
        $result = $isRoot->invoke($this->Table, $posting);
        $this->assertTrue($result);

        $posting = ['pid' => 1];
        $result = $isRoot->invoke($this->Table, $posting);
        $this->assertFalse($result);
    }

    public function testTreeForNode()
    {
        $posting = $this->Table->treeForNode(2);
        $this->assertEquals(2, $posting->get('id'));

        $children = $posting->getAllChildren();
        $this->assertEquals(3, count($children));
        $this->assertEquals(3, array_shift($children)->get('id'));
    }

    public function testGetThreadId()
    {
        $result = $this->Table->getThreadId(1);
        $expected = 1;
        $this->assertEquals($result, $expected);

        $result = $this->Table->getThreadId(5);
        $expected = 4;
        $this->assertEquals($result, $expected);
    }

    public function testGetThreadIdNotFound()
    {
        $this->expectException('\UnexpectedValueException');
        $this->Table->getThreadId(999);
    }
}
