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

use App\Lib\Model\Table\FieldFilter;
use App\Model\Entity\Entry;
use App\Model\Table\EntriesTable;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Posting;
use Saito\Posting\PostingInterface;
use Saito\Posting\TreeBuilder;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;
use Traversable;

class PostingBehavior extends Behavior
{
    /** @var CurrentUserInterface */
    private $CurrentUser;

    /** @var FieldFilter */
    private $fieldFilter;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->fieldFilter = (new fieldfilter())
            ->setConfig('create', ['category_id', 'pid', 'subject', 'text'])
            ->setConfig('update', ['category_id', 'subject', 'text']);
    }

    /**
     * Creates a new posting from user
     *
     * @param array $data raw posting data
     * @param CurrentUserInterface $CurrentUser the current user
     * @return Entry|null on success, null otherwise
     */
    public function createPosting(array $data, CurrentUserInterface $CurrentUser): ?Entry
    {
        $data = $this->fieldFilter->filterFields($data, 'create');

        if (!empty($data['pid'])) {
            /// new posting is answer to existing posting
            $parent = $this->getTable()->get($data['pid']);

            if (empty($parent)) {
                throw new \InvalidArgumentException(
                    'Parent posting for creating a new answer not found.',
                    1564756571
                );
            }

            $data = $this->prepareChildPosting($parent, $data);
        } else {
            /// if no pid is provided the new posting is root-posting
            $data['pid'] = 0;
        }

        /// set user who created the posting
        $data['user_id'] = $CurrentUser->getId();
        $data['name'] = $CurrentUser->get('username');

        $this->validatorSetup($CurrentUser);

        /** @var EntriesTable */
        $table = $this->getTable();

        return $table->createEntry($data);
    }

    /**
     * Updates an existing posting
     *
     * @param Entry $posting the posting to update
     * @param array $data data the posting should be updated with
     * @param CurrentUserInterface $CurrentUser the current-user
     * @return Entry|null the posting which was asked to update
     */
    public function updatePosting(Entry $posting, array $data, CurrentUserInterface $CurrentUser): ?Entry
    {
        $data = $this->fieldFilter->filterFields($data, 'update');
        $isRoot = $posting->isRoot();
        $parent = $this->getTable()->get($posting->get('pid'));

        if (!$isRoot) {
            $data = $this->prepareChildPosting($parent, $data);
        }

        $data['edited_by'] = $CurrentUser->get('username');

        /// must be set for validation
        $data['locked'] = $posting->get('locked');
        $data['fixed'] = $posting->get('fixed');

        $data['pid'] = $posting->get('pid');
        $data['time'] = $posting->get('time');
        $data['user_id'] = $posting->get('user_id');

        $this->validatorSetup($CurrentUser);
        $this->getTable()->getValidator()->add(
            'edited_by',
            'isEditingAllowed',
            ['rule' => [$this, 'validateEditingAllowed']]
        );

        /** @var EntriesTable */
        $table = $this->getTable();

        return $table->updateEntry($posting, $data);
    }

    /**
     * Populates data of an answer derived from parent the parent-posting
     *
     * @param BasicPostingInterface $parent parent data
     * @param array $data current posting data
     * @return array populated $data
     */
    public function prepareChildPosting(BasicPostingInterface $parent, array $data): array
    {
        if (empty($data['subject'])) {
            // if new subject is empty use the parent's subject
            $data['subject'] = $parent->get('subject');
        }

        $data['category_id'] = $parent->get('category_id');
        $data['tid'] = $parent->get('tid');

        return $data;
    }

    /**
     * Sets-up validator for the table
     *
     * @param CurrentUserInterface $CurrentUser current user
     * @return void
     */
    private function validatorSetup(CurrentUserInterface $CurrentUser): void
    {
        $this->CurrentUser = $CurrentUser;

        $this->getTable()->getValidator()->add(
            'category_id',
            'isAllowed',
            ['rule' => [$this, 'validateCategoryIsAllowed']]
        );
    }

    /**
     * check that entries are only in existing and allowed categories
     *
     * @param mixed $categoryId value
     * @param array $context context
     * @return bool
     */
    public function validateCategoryIsAllowed($categoryId, $context): bool
    {
        $isRoot = $context['data']['pid'] == 0;
        $action = $isRoot ? 'thread' : 'answer';

        // @td better return !$posting->isAnsweringForbidden();
        return $this->CurrentUser->getCategories()->permission($action, $categoryId);
    }

    /**
     * check editing allowed
     *
     * @param mixed $check value
     * @param array $context context
     * @return bool
     */
    public function validateEditingAllowed($check, $context): bool
    {
        $posting = (new Posting($context['data']))->withCurrentUser($this->CurrentUser);

        return $posting->isEditingAllowed();
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
}
