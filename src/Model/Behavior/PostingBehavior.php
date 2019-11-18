<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Behavior;

use App\Model\Table\EntriesTable;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Saito\Posting\Posting;
use Saito\Posting\PostingInterface;
use Saito\Posting\TreeBuilder;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;
use Traversable;

class PostingBehavior extends Behavior
{
    /**
     * {@inheritDoc}
     */
    public function buildRules(Event $event, RulesChecker $rules)
    {
        $rules->add(
            function ($entity) {
                return $entity->isDirty('locked') ? ($entity->get('pid') === 0) : true;
            },
            'checkOnlyRootCanBeLocked',
            [
                'errorField' => 'locked',
                'message' => 'Only a root posting can be locked.',
            ]
        );

        $rules->addUpdate(
            function ($entity) {
                if ($entity->isDirty('category_id')) {
                    return $entity->isRoot();
                }

                return true;
            },
            'checkCategoryChangeOnlyOnRootPostings',
            [
                'errorField' => 'category_id',
                'message' => 'Cannot change category on non-root-postings.',
            ]
        );

        $rules->add($rules->existsIn('category_id', 'Categories'));

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave(Event $event, Entity $entity)
    {
        $success = true;

        /// change category of thread if category of root entry changed
        if (!$entity->isNew() && $entity->isDirty('category_id')) {
            $success &= $this->threadChangeCategory(
                $entity->get('id'),
                $entity->get('category_id')
            );
        }

        if (!$success) {
            $event->stopPropagation();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(Event $event, Entity $entity)
    {
        if ($entity->isDirty('locked')) {
            $this->lockThread($entity->get('tid'), $entity->get('locked'));
        }
    }

    /**
     * Get an array of postings for threads
     *
     * @param array $tids Thread-IDs
     * @param array|null $order Thread sort order
     * @param CurrentUserInterface $CU Current User
     * @return array<PostingInterface> Array of postings found
     * @throws RecordNotFoundException If no thread is found
     */
    public function postingsForThreads(array $tids, ?array $order = null, CurrentUserInterface $CU = null): array
    {
        $entries = $this->getTable()
            ->find('entriesForThreads', ['threadOrder' => $order, 'tids' => $tids])
            ->all();

        if (!count($entries)) {
            throw new RecordNotFoundException(
                sprintf('No postings for thread-IDs "%s".', implode(', ', $tids))
            );
        }

        return $this->entriesToPostings($entries, $CU);
    }

    /**
     * Get a posting for a thread
     *
     * @param int $tid Thread-ID
     * @param bool $complete complete fieldset
     * @param CurrentUserInterface|null $CurrentUser CurrentUser
     * @return PostingInterface
     * @throws RecordNotFoundException If thread isn't found
     */
    public function postingsForThread(int $tid, bool $complete = false, ?CurrentUserInterface $CurrentUser = null): PostingInterface
    {
        $entries = $this->getTable()
            ->find('entriesForThreads', ['complete' => $complete, 'tids' => [$tid]])
            ->all();

        if (!count($entries)) {
            throw new RecordNotFoundException(
                sprintf('No postings for thread-ID "%s".', $tid)
            );
        }

        $postings = $this->entriesToPostings($entries, $CurrentUser);

        return reset($postings);
    }

    /**
     * Delete a node
     *
     * @param int $id the node id
     * @return bool
     */
    public function deletePosting(int $id): bool
    {
        $root = $this->postingsForNode($id);
        if (empty($root)) {
            throw new \InvalidArgumentException();
        }

        $nodesToDelete[] = $root;
        $nodesToDelete = array_merge($nodesToDelete, $root->getAllChildren());

        $idsToDelete = [];
        foreach ($nodesToDelete as $node) {
            $idsToDelete[] = $node->get('id');
        };

        /** @var EntriesTable */
        $table = $this->getTable();

        return $table->deleteWithIds($idsToDelete);
    }

    /**
     * Get recent postings
     *
     * ### Options:
     *
     * - `user_id` int|<null> If provided finds only postings of that user.
     * - `limit` int <10> Number of postings to find.
     *
     * @param CurrentUserInterface $User User who has access to postings
     * @param array $options find options
     *
     * @return array<PostingInterface> Array of Postings
     */
    public function getRecentPostings(CurrentUserInterface $User, array $options = []): array
    {
        Stopwatch::start('PostingBehavior::getRecentPostings');

        $options += [
            'user_id' => null,
            'limit' => 10,
        ];

        $options['category_id'] = $User->getCategories()->getAll('read');

        $read = function () use ($options) {
            $conditions = [];
            if ($options['user_id'] !== null) {
                $conditions[]['Entries.user_id'] = $options['user_id'];
            }
            if ($options['category_id'] !== null) {
                $conditions[]['Entries.category_id IN'] = $options['category_id'];
            };

            $result = $this
                ->getTable()
                ->find(
                    'entry',
                    [
                        'conditions' => $conditions,
                        'limit' => $options['limit'],
                        'order' => ['time' => 'DESC']
                    ]
                )
                // hydrating kills performance
                ->enableHydration(false)
                ->all();

            return $result;
        };

        $key = 'Entry.recentEntries-' . md5(serialize($options));
        $results = Cache::remember($key, $read, 'entries');

        $threads = [];
        foreach ($results as $result) {
            $threads[$result['id']] = (new Posting($result))->withCurrentUser($User);
        }

        Stopwatch::stop('PostingBehavior::getRecentPostings');

        return $threads;
    }

    /**
     * Convert array with Entry entities to array with Postings
     *
     * @param Traversable $entries Entry array
     * @param CurrentUserInterface|null $CurrentUser The current user
     * @return array<PostingInterface>
     */
    protected function entriesToPostings(Traversable $entries, ?CurrentUserInterface $CurrentUser = null): array
    {
        Stopwatch::start('PostingBehavior::entriesToPostings');
        $threads = [];
        $postings = (new TreeBuilder())->build($entries);
        foreach ($postings as $thread) {
            $posting = new Posting($thread);
            if ($CurrentUser) {
                $posting->withCurrentUser($CurrentUser);
            }
            $threads[$thread['tid']] = $posting;
        }
        Stopwatch::stop('PostingBehavior::entriesToPostings');

        return $threads;
    }

    /**
     * tree of a single node and its subentries
     *
     * @param int $id id
     * @return PostingInterface|null tree or null if nothing found
     */
    protected function postingsForNode(int $id) : ?PostingInterface
    {
        /** @var EntriesTable */
        $table = $this->getTable();
        $tid = $table->getThreadId($id);
        $postings = $this->postingsForThreads([$tid]);
        $postings = array_shift($postings);

        return $postings->getThread()->get($id);
    }

    /**
     * Finder to get all entries for threads
     *
     * @param Query $query Query
     * @param array $options Options
     * - 'tids' array required thread-IDs
     * - 'complete' fieldset
     * - 'threadOrder' order
     * @return Query
     */
    public function findEntriesForThreads(Query $query, array $options): Query
    {
        Stopwatch::start('PostingBehavior::findEntriesForThreads');
        $options += [
            'complete' => false,
            'threadOrder' => ['last_answer' => 'ASC'],
        ];
        if (empty($options['tids'])) {
            throw new \InvalidArgumentException('Not threads to find.');
        }
        $tids = $options['tids'];
        $order = $options['threadOrder'];
        unset($options['threadOrder'], $options['tids']);

        $query = $query->find('entry', $options)
            ->where(['tid IN' => $tids])
            ->order($order)
            // hydrating kills performance
            ->enableHydration(false);
        Stopwatch::stop('PostingBehavior::findEntriesForThreads');

        return $query;
    }

    /**
     * Locks or unlocks a thread
     *
     * The lock operation is supposed to be done on the root entry.
     * All other entries in the same thread are set to locked too.
     *
     * @param int $tid ID of the thread to lock
     * @param bool $locked True to lock, false to unlock
     * @return void
     */
    protected function lockThread(int $tid, $locked = true)
    {
        $this->getTable()->updateAll(['locked' => $locked], ['tid' => $tid]);
    }

    /**
     * Changes the category of a thread.
     *
     * Assigns the new category-id to all postings in that thread.
     *
     * @param int $tid thread-ID
     * @param int $newCategoryId id for new category
     * @return bool success
     * @throws RecordNotFoundException
     */
    protected function threadChangeCategory(int $tid, int $newCategoryId): bool
    {
        $affected = $this->getTable()->updateAll(
            ['category_id' => $newCategoryId],
            ['tid' => $tid]
        );

        return $affected > 0;
    }

    /**
     * Merge thread on to entry $targetId
     *
     * @param int $sourceId root-id of the posting that is merged onto another
     *     thread
     * @param int $targetId id of the posting the source-thread should be
     *     appended to
     * @return bool true if merge was successfull false otherwise
     */
    public function threadMerge(int $sourceId, int $targetId): bool
    {
        /** @var EntriesTable */
        $table = $this->getTable();

        $sourcePosting = $table->get($sourceId, ['return' => 'Entity']);

        // check that source is thread-root and not an subposting
        if (!$sourcePosting->isRoot()) {
            return false;
        }

        $targetPosting = $table->get($targetId);

        // check that target exists
        if (!$targetPosting) {
            return false;
        }

        // check that a thread is not merged onto itself
        if ($targetPosting->get('tid') === $sourcePosting->get('tid')) {
            return false;
        }

        // set target entry as new parent entry
        $table->patchEntity(
            $sourcePosting,
            ['pid' => $targetPosting->get('id')]
        );
        if ($table->save($sourcePosting)) {
            // associate all entries in source thread to target thread
            $table->updateAll(
                ['tid' => $targetPosting->get('tid')],
                ['tid' => $sourcePosting->get('tid')]
            );

            // appended source entries get category of target thread
            $this->threadChangeCategory(
                $targetPosting->get('tid'),
                $targetPosting->get('category_id')
            );

            // update target thread last answer if source is newer
            $sourceLastAnswer = $sourcePosting->get('last_answer');
            $targetLastAnswer = $targetPosting->get('last_answer');
            if ($sourceLastAnswer->gt($targetLastAnswer)) {
                $targetRoot = $table->get(
                    $targetPosting->get('tid'),
                    ['return' => 'Entity']
                );
                $targetRoot = $table->patchEntity(
                    $targetRoot,
                    ['last_answer' => $sourceLastAnswer]
                );
                $table->save($targetRoot);
            }

            // propagate pinned property from target to source
            $isTargetPinned = $targetPosting->isLocked();
            $isSourcePinned = $sourcePosting->isLocked();
            if ($isSourcePinned !== $isTargetPinned) {
                $this->lockThread($targetPosting->get('tid'), $isTargetPinned);
            }

            $table->dispatchDbEvent(
                'Model.Thread.change',
                ['subject' => $targetPosting->get('tid')]
            );

            return true;
        }

        return false;
    }
}
