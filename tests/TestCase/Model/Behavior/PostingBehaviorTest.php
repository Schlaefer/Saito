<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Model\Behavior;

use Saito\Test\Model\Table\SaitoTableTestCase;

class PostingBehaviorTest extends SaitoTableTestCase
{
    public $tableClass = 'Entries';

    /**
     * @var EntriesTable
     */
    public $Table;

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'plugin.Bookmarks.Bookmark',
    ];

    public function testDeletePostingCompleteThread()
    {
        $tid = 1;

        //= test thread exists before we delete it
        $countBeforeDelete = $this->Table->find()
            ->where(['tid' => $tid])
            ->count();
        $expected = 6;
        $this->assertEquals($countBeforeDelete, $expected);

        $allBookmarksBeforeDelete = $this->Table->Bookmarks->find()->count();

        $result = $this->Table->deletePosting($tid);
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

    public function testDeletePostingSubthread()
    {
        $tid = 1;

        /// test thread exists before we delete it
        $countBeforeDelete = $this->Table->find()
            ->where(['tid' => $tid])
            ->count();
        $expected = 6;
        $this->assertEquals($countBeforeDelete, $expected);

        $this->Table->deletePosting(2);

        $after = $this->Table->find('list', [
            'where' => ['tid' => $tid],
            'keyField' => 'id',
            'valueField' => 'id',
        ])->toArray();

        $this->assertArrayHasKey(1, $after);
        $this->assertArrayHasKey(8, $after);
    }

    public function testLockNotRootEntryFailure()
    {
        $entity = $this->Table->get(2);
        $this->assertFalse($entity->get('locked'));

        $patched = $this->Table->patchEntity($entity, ['locked' => true]);
        $this->Table->save($patched);

        $this->assertTrue($patched->hasErrors());
        $this->assertNotEmpty($patched->getError('locked'));
    }

    public function testLockSuccess()
    {
        $entity = $this->Table->get(1);
        $this->assertEquals(0, $this->Table->find()->where(['tid' => 1, 'locked' => true])->all()->count());

        $patched = $this->Table->patchEntity($entity, ['locked' => true]);
        $this->Table->save($patched);

        $this->assertGreaterThan(1, $this->Table->find()->where(['tid' => 1, 'locked' => true])->all()->count());
    }

    /**
     * Category change is only allowed on root postings
     *
     * That will also change all posting in the root postings thread
     */
    public function testChangeCategoryOnNonRootFailure()
    {
        $posting = $this->Table->get(2, ['return' => 'Entity']);
        $posting->set('category_id', 3);
        $success = $this->Table->save($posting);

        $this->assertFalse($success);
        $errors = $posting->getErrors();
        $this->assertArrayHasKey('checkCategoryChangeOnlyOnRootPostings', $errors['category_id']);
    }

    /**
     * Test changing the category of a thread
     *
     * - Should change category-ID of every posting
     * - Should update the counter-cache for threads in category
     */
    public function testChangeThreadCategory()
    {
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

    /**
     * Test merge
     *
     * Merge thread 2 (root-id: 4) onto entry 2 in thread 1
     */
    public function testThreadMerge()
    {
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
        /// unlock source the fixture thread
        $posting = $this->Table->get(12);
        $this->assertTrue($posting->isLocked());

        /// lock the target fixture thread
        $posting = $this->Table->get(2);
        $this->assertFalse($posting->isLocked());

        /// merge
        $this->Table->threadMerge(1, 12);

        /// test
        $posting = $this->Table->get(2);
        $this->assertTrue($posting->isLocked());
    }

    /**
     * test that a pinned source thread is unpinned before merge
     */
    public function testThreadMergeUnpin()
    {
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

    public function testChangeThreadCategoryNotAnExistingCategory()
    {
        $newCategory = 9999;

        $posting = $this->Table->get(1, ['return' => 'Entity']);
        $this->Table->patchEntity($posting, ['category_id' => $newCategory]);
        $result = $this->Table->save($posting);
        $this->assertFalse($result);
    }
}
