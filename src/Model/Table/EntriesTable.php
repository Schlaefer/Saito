<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppTable;
use App\Model\Entity\Entry;
use App\Model\Table\CategoriesTable;
use Bookmarks\Model\Table\BookmarksTable;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Saito\App\Registry;
use Saito\Posting\Posting;
use Saito\RememberTrait;
use Saito\User\CurrentUser\CurrentUserInterface;
use Search\Manager;
use Stopwatch\Lib\Stopwatch;

/**
 * Stores postings
 *
 * Field notes:
 * - `edited_by` - Came from mylittleforum. @td Should by migrated to User.id.
 * - `name` - Came from mylittleforum. Is still used in fulltext index.
 *
 * @property BookmarksTable $Bookmarks
 * @property CategoriesTable $Categories
 * @method array treeBuild(array $postings)
 * @method createPosting(array $data, CurrentUserInterface $CurrentUser)
 * @method updatePosting(Entry $posting, array $data, CurrentUserInterface $CurrentUser)
 * @method array prepareChildPosting(BasicPostingInterface $parent, array $data)
 */
class EntriesTable extends AppTable
{
    use RememberTrait;

    /**
     * Fields for search plugin
     *
     * @var array
     */
    public $filterArgs = [
        'subject' => ['type' => 'like'],
        'text' => ['type' => 'like'],
        'name' => ['type' => 'like'],
        'category' => ['type' => 'value'],
    ];

    /**
     * field list necessary for displaying a thread_line
     *
     * Entry.text determine if Entry is n/t
     *
     * @var array
     */
    public $threadLineFieldList = [
        'Entries.id',
        'Entries.pid',
        'Entries.tid',
        'Entries.subject',
        'Entries.text',
        'Entries.time',
        'Entries.fixed',
        'Entries.last_answer',
        'Entries.views',
        'Entries.user_id',
        'Entries.locked',
        'Entries.name',
        'Entries.solves',
        'Users.username',
        'Categories.id',
        'Categories.accession',
        'Categories.category',
        'Categories.description'
    ];

    /**
     * fields additional to $threadLineFieldList to show complete entry
     *
     * @var array
     */
    public $showEntryFieldListAdditional = [
        'Entries.edited',
        'Entries.edited_by',
        'Entries.ip',
        'Entries.category_id',
        'Users.id',
        'Users.avatar',
        'Users.signature',
        'Users.user_place'
    ];

    protected $_defaultConfig = [
        'subject_maxlength' => 100
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->setPrimaryKey('id');

        $this->addBehavior('Posting');
        $this->addBehavior('IpLogging');
        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->addBehavior(
            'CounterCache',
            [
                // cache how many postings a user has
                'Users' => ['entry_count'],
                // cache how many threads a category has
                'Categories' => [
                    'thread_count' => function ($event, Entry $entity, $table, $original) {
                        if (!$entity->isRoot()) {
                            return false;
                        }
                        // posting is moved to new category…
                        if ($original) {
                            // update old category (should decrement counter)
                            $categoryId = $entity->getOriginal('category_id');
                        } else {
                            // update new category (increment counter)
                            $categoryId = $entity->get('category_id');
                        }

                        $query = $table->find('all', ['conditions' => [
                            'pid' => 0, 'category_id' => $categoryId
                        ]]);
                        $count = $query->count();

                        return $count;
                    }
                ]
            ]
        );

        $this->belongsTo('Categories', ['foreignKey' => 'category_id']);
        $this->belongsTo('Users', ['foreignKey' => 'user_id']);

        $this->hasMany(
            'Bookmarks',
            ['foreignKey' => 'entry_id', 'dependent' => true]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator->setProvider(
            'saito',
            'Saito\Validation\SaitoValidationProvider'
        );

        /// category_id
        $validator
            ->notEmpty('category_id')
            ->requirePresence('category_id', 'create')
            ->add(
                'category_id',
                [
                    'numeric' => ['rule' => 'numeric'],
                    'assoc' => [
                        'rule' => ['validateAssoc', 'Categories'],
                        'last' => true,
                        'provider' => 'saito'
                    ]
                ]
            );

        /// last_answer
        $validator
            ->requirePresence('last_answer', 'create')
            ->notEmptyDateTime('last_answer', null, 'create');

        /// name
        $validator
            ->requirePresence('name', 'create')
            ->notEmptyString('name', null, 'create');

        /// pid
        $validator->requirePresence('pid', 'create');

        /// subject
        $validator
            ->notEmptyString('subject', __d('validation', 'entries.subject.notEmpty'))
            ->requirePresence('subject', 'create')
            ->add(
                'subject',
                [
                    'maxLength' => [
                        'rule' => [$this, 'validateSubjectMaxLength'],
                        'message' => __d(
                            'validation',
                            'entries.subject.maxlength'
                        )
                    ]
                ]
            );

        /// time
        $validator
            ->requirePresence('time', 'create')
            ->notEmptyDateTime('time', null, 'create');

        /// user_id
        $validator
            ->requirePresence('user_id', 'create')
            ->add('user_id', ['numeric' => ['rule' => 'numeric']]);

        /// views
        $validator->add(
            'views',
            ['comparison' => ['rule' => ['comparison', '>=', 0]]]
        );

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules = parent::buildRules($rules);

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

        return $rules;
    }

    /**
     * Advanced search configuration from SaitoSearch plugin
     *
     * @see https://github.com/FriendsOfCake/search
     *
     * @return Manager
     */
    public function searchManager(): Manager
    {
        /** @var Manager $searchManager */
        $searchManager = $this->getBehavior('Search')->searchManager();
        $searchManager
        ->like('subject', [
            'before' => true,
            'after' => true,
            'fieldMode' => 'OR',
            'comparison' => 'LIKE',
            'wildcardAny' => '*',
            'wildcardOne' => '?',
            'field' => ['subject'],
            'filterEmpty' => true,
        ])
        ->like('text', [
            'before' => true,
            'after' => true,
            'fieldMode' => 'OR',
            'comparison' => 'LIKE',
            'wildcardAny' => '*',
            'wildcardOne' => '?',
            'field' => ['text'],
            'filterEmpty' => true,
        ])
        ->value('name', ['filterEmpty' => true]);

        return $searchManager;
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
     * @return array Array of Postings
     */
    public function getRecentEntries(
        CurrentUserInterface $User,
        array $options = []
    ) {
        Stopwatch::start('Model->User->getRecentEntries()');

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
                ->find(
                    'all',
                    [
                        'contain' => ['Users', 'Categories'],
                        'fields' => $this->threadLineFieldList,
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
            $threads[$result['id']] = Registry::newInstance(
                '\Saito\Posting\Posting',
                ['rawData' => $result]
            );
        }

        Stopwatch::stop('Model->User->getRecentEntries()');

        return $threads;
    }

    /**
     * Finds the thread-id for a posting
     *
     * @param int $id Posting-Id
     * @return int Thread-Id
     * @throws \UnexpectedValueException
     */
    public function getThreadId($id)
    {
        $entry = $this->find(
            'all',
            ['conditions' => ['id' => $id], 'fields' => 'tid']
        )->first();
        if (empty($entry)) {
            throw new \UnexpectedValueException(
                'Posting not found. Posting-Id: ' . $id
            );
        }

        return $entry->get('tid');
    }

    /**
     * Shorthand for reading an entry with full data
     *
     * @param int $primaryKey key
     * @param array $options options
     * @return mixed Posting if found false otherwise
     */
    public function get($primaryKey, $options = [])
    {
        $options += ['return' => 'Posting'];
        $return = $options['return'];
        unset($options['return']);

        /** @var Entry */
        $result = $this->find('entry')
            ->where([$this->getAlias() . '.id' => $primaryKey])
            ->first();

        if (!$result) {
            return false;
        }

        switch ($return) {
            case 'Posting':
                return $result->toPosting();
            case 'Entity':
            default:
                return $result;
        }
    }

    /**
     * get parent id
     *
     * @param int $id id
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function getParentId($id)
    {
        $entry = $this->find()->select('pid')->where(['id' => $id])->first();
        if (!$entry) {
            throw new \UnexpectedValueException(
                'Posting not found. Posting-Id: ' . $id
            );
        }

        return $entry->get('pid');
    }

    /**
     * creates a new root or child entry for a node
     *
     * fields in $data are filtered
     *
     * @param array $data data
     * @return Entry|null on success, null otherwise
     */
    public function createEntry(array $data): ?Entry
    {
        $data['time'] = bDate();
        $data['last_answer'] = bDate();

        /** @var Entry */
        $posting = $this->newEntity($data);
        $errors = $posting->getErrors();
        if (!empty($errors)) {
            return $posting;
        }

        $posting = $this->save($posting);
        if (!$posting) {
            return null;
        }

        $id = $posting->get('id');
        /** @var Entry */
        $posting = $this->get($id, ['return' => 'Entity']);

        if ($posting->isRoot()) {
            // posting started a new thread, so set thread-ID to posting's own ID
            /** @var Entry */
            $posting = $this->patchEntity($posting, ['tid' => $id]);
            if (!$this->save($posting)) {
                return $posting;
            }

            $this->_dispatchEvent('Model.Thread.create', ['subject' => $id, 'data' => $posting]);
        } else {
            // update last answer time of root entry
            $this->updateAll(
                ['last_answer' => $posting->get('last_answer')],
                ['id' => $posting->get('tid')]
            );

            $eventData = ['subject' => $posting->get('pid'), 'data' => $posting];
            $this->_dispatchEvent('Model.Entry.replyToEntry', $eventData);
            $this->_dispatchEvent('Model.Entry.replyToThread', $eventData);
        }

        return $posting;
    }

    /**
     * Updates a posting
     *
     * fields in $data are filtered except for $id!
     *
     * @param Entry $posting Entity
     * @param array $data data
     * @return Entry|null
     */
    public function updateEntry(Entry $posting, array $data): ?Entry
    {
        $data['id'] = $posting->get('id');
        $data['edited'] = bDate();

        /** @var Entry */
        $patched = $this->patchEntity($posting, $data);
        $errors = $patched->getErrors();
        if (!empty($errors)) {
            return $patched;
        }

        /** @var Entry */
        $new = $this->save($posting);
        if (!$new) {
            return null;
        }

        $this->_dispatchEvent(
            'Model.Entry.update',
            ['subject' => $posting->get('id'), 'data' => $posting]
        );

        return $new;
    }

    /**
     * tree of a single node and its subentries
     *
     * $options = array(
     *    'root' => true // performance improvements if it's a known thread-root
     * );
     *
     * @param int $id id
     * @param array $options options
     * @return Posting|null tree or null if nothing found
     */
    public function treeForNode(int $id, ?array $options = []): ?Posting
    {
        $options += [
            'root' => false,
            'complete' => false
        ];

        if ($options['root']) {
            $tid = $id;
        } else {
            $tid = $this->getThreadId($id);
        }

        $fields = null;
        if ($options['complete']) {
            $fields = array_merge(
                $this->threadLineFieldList,
                $this->showEntryFieldListAdditional
            );
        }

        $tree = $this->treesForThreads([$tid], null, $fields);

        if (!$tree) {
            return null;
        }

        $tree = reset($tree);

        //= extract subtree
        if ((int)$tid !== (int)$id) {
            $tree = $tree->getThread()->get($id);
        }

        return $tree;
    }

    /**
     * trees for multiple tids
     *
     * @param array $ids ids
     * @param array $order order
     * @param array $fieldlist fieldlist
     * @return array|null array of Postings, null if nothing found
     */
    public function treesForThreads(array $ids, ?array $order = null, array $fieldlist = null): ?array
    {
        if (empty($ids)) {
            return [];
        }

        if (empty($order)) {
            $order = ['last_answer' => 'ASC'];
        }

        if ($fieldlist === null) {
            $fieldlist = $this->threadLineFieldList;
        }

        Stopwatch::start('EntriesTable::treesForThreads() DB');
        $postings = $this->_getThreadEntries(
            $ids,
            ['order' => $order, 'fields' => $fieldlist]
        );
        Stopwatch::stop('EntriesTable::treesForThreads() DB');

        if (!$postings->count()) {
            return null;
        }

        Stopwatch::start('EntriesTable::treesForThreads() CPU');
        $threads = [];
        $postings = $this->treeBuild($postings);
        foreach ($postings as $thread) {
            $id = $thread['tid'];
            $threads[$id] = $thread;
            $threads[$id] = Registry::newInstance(
                '\Saito\Posting\Posting',
                ['rawData' => $thread]
            );
        }
        Stopwatch::stop('EntriesTable::treesForThreads() CPU');

        return $threads;
    }

    /**
     * Returns all entries of threads $tid
     *
     * @param array $tid ids
     * @param array $params params
     * - 'fields' array of thread-ids: [1, 2, 5]
     * - 'order' sort order for threads ['time' => 'ASC'],
     * @return mixed unhydrated result set
     */
    protected function _getThreadEntries(array $tid, array $params = [])
    {
        $params += [
            'fields' => $this->threadLineFieldList,
            'order' => ['last_answer' => 'ASC']
        ];

        $threads = $this
            ->find(
                'all',
                [
                    'conditions' => ['tid IN' => $tid],
                    'contain' => ['Users', 'Categories'],
                    'fields' => $params['fields'],
                    'order' => $params['order']
                ]
            )
            // hydrating kills performance
            ->enableHydration(false);

        return $threads;
    }

    /**
     * Marks a sub-entry as solution to a root entry
     *
     * @param Entry $posting posting to toggle
     * @return bool success
     */
    public function toggleSolve(Entry $posting)
    {
        if ($posting->get('solves')) {
            $value = 0;
        } else {
            $value = $posting->get('tid');
        }

        $this->patchEntity($posting, ['solves' => $value]);
        if (!$this->save($posting)) {
            return false;
        }

        $this->_dispatchEvent(
            'Model.Entry.update',
            ['subject' => $posting->get('id'), 'data' => $posting]
        );

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function toggle($id, $key)
    {
        $result = parent::toggle($id, $key);
        if ($key === 'locked') {
            $this->_threadLock($id, $result);
        }

        $entry = $this->get($id);
        $this->_dispatchEvent(
            'Model.Entry.update',
            [
                'subject' => $entry->get('id'),
                'data' => $entry
            ]
        );

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeValidate(
        Event $event,
        Entity $entity,
        \ArrayObject $options,
        Validator $validator
    ) {
        //= in n/t posting delete unnecessary body text
        // @bogus move to entity?
        if ($entity->isDirty('text')) {
            $entity->set('text', rtrim($entity->get('text')));
        }
    }

    /**
     * Deletes posting incl. all its subposting and associated data
     *
     * @param int $id id
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return bool
     */
    public function treeDeleteNode($id)
    {
        $root = $this->treeForNode((int)$id);

        if (empty($root)) {
            throw new \Exception;
        }

        $nodesToDelete[] = $root;
        $nodesToDelete = array_merge($nodesToDelete, $root->getAllChildren());

        $idsToDelete = [];
        foreach ($nodesToDelete as $node) {
            $idsToDelete[] = $node->get('id');
        };

        $success = $this->deleteAll(['id IN' => $idsToDelete]);

        if (!$success) {
            return false;
        }

        $this->Bookmarks->deleteAll(['entry_id IN' => $idsToDelete]);

        $this->dispatchSaitoEvent(
            'Model.Saito.Posting.delete',
            ['subject' => $root, 'table' => $this]
        );

        return true;
    }

    /**
     * Anonymizes the entries for a user
     *
     * @param int $userId user-ID
     * @return void
     */
    public function anonymizeEntriesFromUser(int $userId): void
    {
        // remove username from all entries and reassign to anonyme user
        $success = (bool)$this->updateAll(
            [
                'edited_by' => null,
                'ip' => null,
                'name' => null,
                'user_id' => 0,
            ],
            ['user_id' => $userId]
        );

        if ($success) {
            $this->_dispatchEvent('Cmd.Cache.clear', ['cache' => 'Thread']);
        }
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
    public function threadMerge($sourceId, $targetId)
    {
        $sourcePosting = $this->get($sourceId, ['return' => 'Entity']);

        // check that source is thread-root and not an subposting
        if (!$sourcePosting->isRoot()) {
            return false;
        }

        $targetPosting = $this->get($targetId);

        // check that target exists
        if (!$targetPosting) {
            return false;
        }

        // check that a thread is not merged onto itself
        if ($targetPosting->get('tid') === $sourcePosting->get('tid')) {
            return false;
        }

        // set target entry as new parent entry
        $this->patchEntity(
            $sourcePosting,
            ['pid' => $targetPosting->get('id')]
        );
        if ($this->save($sourcePosting)) {
            // associate all entries in source thread to target thread
            $this->updateAll(
                ['tid' => $targetPosting->get('tid')],
                ['tid' => $sourcePosting->get('tid')]
            );

            // appended source entries get category of target thread
            $this->_threadChangeCategory(
                $targetPosting->get('tid'),
                $targetPosting->get('category_id')
            );

            // update target thread last answer if source is newer
            $sourceLastAnswer = $sourcePosting->get('last_answer');
            $targetLastAnswer = $targetPosting->get('last_answer');
            if ($sourceLastAnswer->gt($targetLastAnswer)) {
                $targetRoot = $this->get(
                    $targetPosting->get('tid'),
                    ['return' => 'Entity']
                );
                $targetRoot = $this->patchEntity(
                    $targetRoot,
                    ['last_answer' => $sourceLastAnswer]
                );
                $this->save($targetRoot);
            }

            // propagate pinned property from target to source
            $isTargetPinned = $targetPosting->isLocked();
            $isSourcePinned = $sourcePosting->isLocked();
            if ($isSourcePinned !== $isTargetPinned) {
                $this->_threadLock($targetPosting->get('tid'), $isTargetPinned);
            }

            $this->_dispatchEvent(
                'Model.Thread.change',
                ['subject' => $targetPosting->get('tid')]
            );

            return true;
        }

        return false;
    }

    /**
     * Implements the custom find type 'entry'
     *
     * @param Query $query query
     * @return Query
     */
    public function findEntry(Query $query)
    {
        $fields = array_merge(
            $this->threadLineFieldList,
            $this->showEntryFieldListAdditional
        );
        $query->select($fields)->contain(['Users', 'Categories']);

        return $query;
    }

    /**
     * Implements the custom find type 'index paginator'
     *
     * @param Query $query query
     * @param array $options finder options
     * @return Query
     */
    public function findIndexPaginator(Query $query, array $options)
    {
        $query
            ->select(['id', 'pid', 'tid', 'time', 'last_answer', 'fixed'])
            ->where(['Entries.pid' => 0]);

        if (!empty($options['counter'])) {
            $query->counter($options['counter']);
        }

        return $query;
    }

    /**
     * Un-/Locks thread: sets posting in thread $tid to $locked
     *
     * @param int $tid thread-ID
     * @param bool $locked flag
     * @return void
     */
    protected function _threadLock($tid, $locked)
    {
        $this->updateAll(['locked' => $locked], ['tid' => $tid]);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave(Event $event, Entity $entity)
    {
        $success = true;

        /// change category of thread if category of root entry changed
        if (!$entity->isNew() && $entity->isDirty('category_id')) {
            $success &= $this->_threadChangeCategory(
                // rules checks that only roots are allowed to change category, so tid = id
                $entity->get('id'),
                $entity->get('category_id')
            );
        }

        if (!$success) {
            $event->stopPropagation();
        }
    }

    /**
     * check subject max length
     *
     * @param mixed $subject subject
     * @return bool
     */
    public function validateSubjectMaxLength($subject)
    {
        return mb_strlen($subject) <= $this->getConfig('subject_maxlength');
    }

    /**
     * Changes the category of a thread.
     *
     * Assigns the new category-id to all postings in that thread.
     *
     * @param int $tid thread-ID
     * @param int $newCategoryId id for new category
     * @return bool success
     * @throws NotFoundException
     */
    protected function _threadChangeCategory(int $tid, int $newCategoryId): bool
    {
        $exists = $this->Categories->exists($newCategoryId);
        if (!$exists) {
            throw new NotFoundException();
        }
        $affected = $this->updateAll(
            ['category_id' => $newCategoryId],
            ['tid' => $tid]
        );

        return $affected > 0;
    }
}
