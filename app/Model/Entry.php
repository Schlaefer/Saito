<?php

	use Saito\User\ForumsUserInterface;

	App::uses('AppModel', 'Model');

/**
 *
 *
 * Model notes
 * ===========
 *
 * Entry.name
 * ----------
 *
 * Came from mlf. Is still used in fulltext index.
 *
 * Entry.edited_by
 * ---------------
 *
 * Came from mlf.
 *
 * @td After mlf is gone `edited_by` should conatin a User.id, not the username string.
 *
 */
	class Entry extends AppModel {

		public $name = 'Entry';

		public $primaryKey = 'id';

		public $actsAs = [
			'Markup',
			'Containable',
			'Search.Searchable',
			'Tree'
		];

		public $findMethods = array(
			'feed' => true,
			'entry' => true
		);

		/**
		 * Fields for search plugin
		 *
		 * @var array
		 */
		public $filterArgs = [
				'subject' => ['type' => 'like'],
				'text' => ['type' => 'like'],
				'name' => ['type' => 'like'],
				'category_id' => ['type' => 'value'],
		];

		public $belongsTo = array(
			'Category' => array(
				'className' => 'Category',
				'foreignKey' => 'category_id',
			),
			'User' => array(
				'className' => 'User',
				'foreignKey' => 'user_id',
				'counterCache' => true,
			),
		);

		public $hasMany = array(
			'Bookmark' => array(
				'foreignKey' => 'entry_id',
				'dependent' => true,
			),
			'Esevent' => array(
				'foreignKey' => 'subject',
				'conditions' => array('Esevent.subject' => 'Entry.id'),
			),
			'UserRead' => [
				'foreignKey' => 'entry_id',
				'dependent' => true
			]
		);

		public $validate = [
				'subject' => [
						'notEmpty' => ['rule' => 'notBlank'],
						'maxLength' => ['rule' => 'validateSubjectMaxLength']
				],
				'category_id' => [
						'notEmpty' => ['rule' => 'notBlank'],
						'numeric' => ['rule' => 'numeric'],
						'isAllowed' => ['rule' => 'validateCategoryIsAllowed']
				],
				'user_id' => ['rule' => 'numeric'],
				'views' => ['rule' => ['comparison', '>=', 0]],
			// used in full text search
				'name' => array()
		];

		/**
		 * Fields allowed in public output
		 *
		 * @var array
		 */
		protected $_allowedPublicOutputFields = [
			'Entry.id',
			'Entry.pid',
			'Entry.tid',
			'Entry.time',
			'Entry.last_answer',
			'Entry.edited',
			'Entry.edited_by',
			'Entry.user_id',
			'Entry.name',
			'Entry.subject',
			'Entry.category_id',
			'Entry.text',
			'Entry.locked',
			'Entry.fixed',
			'Entry.views',
			'User.username'
		];

		/**
		 * field list necessary for displaying a thread_line
		 *
		 * Entry.text determine if Entry is n/t
		 *
		 * @var string
		 */
		public $threadLineFieldList = [
			'Entry.id',
			'Entry.pid',
			'Entry.tid',
			'Entry.subject',
			'Entry.text',
			'Entry.time',
			'Entry.fixed',
			'Entry.last_answer',
			'Entry.views',
			'Entry.user_id',
			'Entry.locked',
			'Entry.name',
			'Entry.solves',

			'User.username',

			'Category.accession',
			'Category.category',
			'Category.description'
		];

		/**
		 * fields additional to $threadLineFieldList to show complete entry
		 *
		 * @var string
		 */
		public $showEntryFieldListAdditional = [
			'Entry.edited',
			'Entry.edited_by',
			'Entry.ip',
			'Entry.category_id',

			'User.id',
			'User.signature',
			'User.user_place'
		];

		/**
		 * Allowed external user input
		 *
		 * @var array
		 */
		public $allowedInputFields = [
			'create' => [
				'category_id',
				'pid',
				'subject',
				'text'
			],
			'update' => [
				'id',
				'category_id',
				'subject',
				'text'
			]
		];

		protected $_settings = [
			'edit_period' => 20,
			'subject_maxlength' => 100
		];

/**
 * Caching for isRoot()
 *
 * @var array
 */
		protected $_isRoot = [];

		/**
		 * @param ForumsUserInterface $User
		 * @param array $options
		 * @return array|mixed
		 */
		public function getRecentEntries(CurrentUserComponent $User, array $options = []) {
			Stopwatch::start('Model->User->getRecentEntries()');

			$options += [
				'user_id' => null,
				'limit' => 10,
				'category_id' => $User->Categories->getAllowed()
			];

			$_cacheKey = 'Entry.recentEntries-' . md5(serialize($options));
			$_cachedEntry = Cache::read($_cacheKey, 'entries');
			if ($_cachedEntry) {
				Stopwatch::stop('Model->User->getRecentEntries()');
				return $_cachedEntry;
			}

			$conditions = array();
			if ($options['user_id'] !== null) {
				$conditions[]['Entry.user_id'] = $options['user_id'];
			}
			if ($options['category_id'] !== null):
				$conditions[]['Entry.category_id'] = $options['category_id'];
			endif;

			$result = $this->find(
				'all',
				[
					'contain' => array('User', 'Category'),
					'fields' => $this->threadLineFieldList,
					'conditions' => $conditions,
					'limit' => $options['limit'],
					'order' => 'time DESC'
				]
			);

			Cache::write($_cacheKey, $result, 'entries');

			Stopwatch::stop('Model->User->getRecentEntries()');
			return $result;
		}

/**
 * Finds the thread-id for a posting
 *
 * @param int $id Posting-Id
 * @return int Thread-Id
 * @throws UnexpectedValueException
 */
		public function getThreadId($id) {
			$entry = $this->find(
				'first',
				[
					'contain' => false,
					'conditions' => ['Entry.id' => $id],
					'fields' => 'Entry.tid'

				]
			);
			if ($entry == false) {
				throw new UnexpectedValueException('Posting not found. Posting-Id: ' . $id);
			}
			return $entry['Entry']['tid'];
		}

/**
 * Shorthand for reading an entry with full data
 */
		public function get($id) {
			return $this->find('entry',
					['conditions' => [$this->alias . '.id' => $id]]);
		}

/**
 * @param $id
 * @return mixed
 * @throws UnexpectedValueException
 */
		public function getParentId($id) {
			$entry = $this->find(
				'first',
				[
					'contain' => false,
					'conditions' => ['Entry.id' => $id],
					'fields' => 'Entry.pid'

				]
			);
			if ($entry == false) {
				throw new UnexpectedValueException('Posting not found. Posting-Id: ' . $id);
			}
			return $entry['Entry']['pid'];
		}

		/**
		 * creates a new root or child entry for a node
		 *
		 * fields in $data are filtered
		 *
		 * @param $data
		 * @return array|bool|mixed
		 */
		public function createPosting($data) {
			if (!isset($data[$this->alias]['pid'])) {
				$data[$this->alias]['pid'] = 0;
			}

			if (isset($data[$this->alias]['subject']) === false) {
				return false;
			}

			try {
				$this->prepare($data, ['preFilterFields' => 'create']);
			} catch (Exception $e) {
				return false;
			}

			$data[$this->alias]['user_id'] = $this->CurrentUser->getId();
			$data[$this->alias]['name'] = $this->CurrentUser['username'];

			$data[$this->alias]['time'] = bDate();
			$data[$this->alias]['last_answer'] = bDate();
			$data[$this->alias]['ip'] = self::_getIp();

			$this->create();
			$_newPosting = $this->save($data);

			if ($_newPosting === false) {
				return false;
			}

			$_newPostingId = $this->id;

			// make sure we pass the complete ['Entry'] dataset to events
			$this->contain();
			$_newPosting = $this->read();

			if ($this->isRoot($data)) {
				// thread-id of new thread is its own id
				if ($this->save(['tid' => $_newPostingId], false, ['tid']) === false) {
					// @td raise error and/or roll back new entry
					return false;
				} else {
					$_newPosting[$this->alias]['tid'] = $_newPostingId;
					$this->Category->id = $data[$this->alias]['category_id'];
					$this->Category->updateThreadCounter();
				}
				$this->_dispatchEvent(
					'Model.Thread.create',
					[
						'subject' => $_newPosting[$this->alias]['id'],
						'data' => $_newPosting
					]
				);
			} else {
				// update last answer time of root entry
				$this->clear();
				$this->id = $_newPosting[$this->alias]['tid'];
				$this->set('last_answer', $_newPosting[$this->alias]['last_answer']);
				if ($this->save() === false) {
					// @td raise error and/or roll back new entry
					return false;
				}

				$this->_dispatchEvent(
					'Model.Entry.replyToEntry',
					[
						'subject' => $_newPosting[$this->alias]['pid'],
						'data' => $_newPosting
					]
				);
				$this->_dispatchEvent(
					'Model.Entry.replyToThread',
					[
						'subject' => $_newPosting[$this->alias]['tid'],
						'data' => $_newPosting
					]
				);
			}

			$this->id = $_newPostingId;
			$_newPosting[$this->alias]['subject'] = Sanitize::html($_newPosting[$this->alias]['subject']);
			$_newPosting[$this->alias]['text'] = Sanitize::html($_newPosting[$this->alias]['text']);
			return $_newPosting;
		}

		/**
		 * Updates a posting
		 *
		 * fields in $data are filtered except for $id!
		 *
		 * @param $data
		 * @param null $CurrentUser
		 * @return array|mixed
		 * @throws NotFoundException
		 * @throws InvalidArgumentException
		 */
		public function update($data, $CurrentUser = null) {
			if ($CurrentUser !== null) {
				$this->CurrentUser = $CurrentUser;
			}

			if (empty($data[$this->alias]['id'])) {
				throw new InvalidArgumentException('Missing entry id in arguments.');
			}

			$id = $data[$this->alias]['id'];
			if (!$this->exists($id)) {
				throw new NotFoundException(sprintf('Entry with id `%s` not found.', $id));
			}

			$this->prepare($data, ['preFilterFields' => 'update']);

			// prevents normal user of changing category of complete thread when answering
			// @todo this should be refactored together with the change category handling in beforeSave()
			if ($this->isRoot($data) === false) {
				unset($data[$this->alias]['category_id']);
			}

			$data[$this->alias]['edited'] = bDate();
			$data[$this->alias]['edited_by'] = $this->CurrentUser['username'];

			$this->validator()->add(
				'edited_by',
				'isEditingAllowed',
				[
					'rule' => 'validateEditingAllowed'
				]
			);

			$result = $this->save($data);

			if ($result) {
				$this->contain();
				$result = $this->read() + $data;
				$this->_dispatchEvent(
					'Model.Entry.update',
					[
						'subject' => $result[$this->alias]['id'],
						'data' => $result
					]
				);
			}

			return $result;
		}

		/**
		 * Update view counter on all entries in a thread
		 *
		 * Note that this function unbinds the model associations for performance.
		 * Otherwise updateAll() left joins all associated models.
		 *
		 * @param $tid thread-ID
		 * @param $uid entries with this user-id are not updated
		 */
		public function threadIncrementViews($tid, $uid = null) {
			Stopwatch::start('Entry::threadIncrementViews');
			$_where = ['Entry.tid' => $tid];
			if ($uid && is_int($uid)) {
				$_where['Entry.user_id !='] = $uid;
			}
			// $_belongsTo = $this->belongsTo;
			$this->unbindModel(['belongsTo' => array_keys($this->belongsTo)]);
			$this->updateAll(['Entry.views' => 'Entry.views + 1'], $_where);
			// $this->bindModel(['belongsTo' => $_belongsTo]);
			Stopwatch::stop('Entry::threadIncrementViews');
		}

/**
 *
 * @mb `views` into extra related table if performance becomes a problem
 */
		public function incrementViews($id, $amount = 1) {
			$this->increment($id, 'views', $amount);
		}

/**
 * tree of a single node and its subentries
 *
 * $options = array(
 *    'root' => true // performance improvements if it's a known thread-root
 * );
 *
 * @param int $id
 * @param array $options
 * @return array tree
 */
		public function treeForNode($id, $options = array()) {
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
				$fields = array_merge($this->threadLineFieldList, $this->showEntryFieldListAdditional);
			}

			$tree = $this->treesForThreads([$tid], null, $fields);

			if ((int)$tid !== (int)$id) {
				$tree = $this->treeGetSubtree($tree, $id);
			}

			return $tree;
		}

		/**
		 * trees for multiple tids
		 *
		 * @param $ids
		 * @param null $order
		 * @param null $fieldlist
		 * @return array|bool false if no threads or array with Posting
		 */
		public function treesForThreads($ids, $order = null, $fieldlist = null) {
			if (empty($ids)) {
				return [];
			}

			Stopwatch::start('Model->Entries->treeForThreads() DB');

			if (empty($order)) {
				$order = 'last_answer ASC';
			}

			if ($fieldlist === null) {
				$fieldlist = $this->threadLineFieldList;
			}

			$entries = $this->_getThreadEntries(
				$ids,
				['order' => $order, 'fields' => $fieldlist]
			);

			Stopwatch::stop('Model->Entries->treeForThreads() DB');
			Stopwatch::start('Model->Entries->treeForThreads() CPU');

			$threads = false;
			if ($entries) {
				$threads = [];
				$entries = $this->treeBuild($entries);
				foreach ($entries as $thread) {
					$id = (int)$thread['Entry']['tid'];
					$threads[$id] = $thread;
				}
			}

			Stopwatch::stop('Model->Entries->treeForThreads() CPU');

			return $threads;
		}

/**
 * @param       $tid
 * @param array $params
 *
 * @return array
 *
 * <pre>
 *  array(
 *      'fields'    => array('Entry.id'),
 *      'order'      => 'time ASC',
 *  )
 * </pre>
 */
		protected function _getThreadEntries($tid, array $params = []) {
			$params += [
				'fields' => $this->threadLineFieldList,
				'order' => 'last_answer ASC'
			];
			$threads = $this->find(
				'all',
				[
					'conditions' => ['tid' => $tid],
					'contain' => ['User', 'Category'],
					'fields' => $params['fields'],
					'order' => $params['order']
				]
			);

			return $threads;
		}

		/**
		 * Marks a sub-entry as solution to a root entry
		 *
		 * @param $id
		 * @return bool
		 * @throws InvalidArgumentException
		 * @throws ForbiddenException
		 */
		public function toggleSolve($id) {
			$entry = $this->get($id);
			if (empty($entry) || $this->isRoot($entry)) {
				throw new InvalidArgumentException;
			}

			$root = $this->get($entry['Entry']['tid']);
			if ((int)$root['User']['id'] !== $this->CurrentUser->getId()) {
				throw new ForbiddenException;
			}

			if ($entry[$this->alias]['solves']) {
				$value = 0;
			} else {
				$value = $entry[$this->alias]['tid'];
			}
			$this->id = $id;
			$success = $this->saveField('solves', $value);
			if (!$success) {
				return $success;
			}
			$this->_dispatchEvent('Model.Entry.update',
					['subject' => $id, 'data' => $entry]);
			$entry[$this->alias]['solves'] = $value;
			return $success;
		}

		public function toggle($key) {
			$result = parent::toggle($key);
			if ($key === 'locked') {
				$this->_threadLock($result);
			}

			$this->contain();
			$entry = $this->read();

			$this->_dispatchEvent(
				'Model.Entry.update',
				[
					'subject' => $entry[$this->alias]['id'],
					'data' => $entry
				]
			);

			return $result;
		}

		public function beforeFind($queryData) {
			parent::beforeFind($queryData);
			/*
				* workarround for possible cakephp join error for associated tables
				* and virtual fields
				*
				* virtualField user_online trouble
				*
				* checkout: maybe association alias name collision
				*
				* checkout in cakephp mailing list/bug tracker
				*/
			$this->User->virtualFields = null;
		}

		public function beforeValidate($options = array()) {
			parent::beforeValidate($options);

			//* in n/t posting delete unnecessary body text
			if (isset($this->data['Entry']['text'])) {
				$this->data['Entry']['text'] = rtrim($this->data['Entry']['text']);
			}
		}

/**
 * Deletes entry and all it's subentries and associated data
 *
 * @return bool
 * @throws Exception
 */
		public function deleteNode($id = null) {
			if (empty($id)) {
				$id = $this->id;
			}

			$this->contain();
			$entry = $this->findById($id);

			if (empty($entry)) {
				throw new Exception;
			}

			$_idsToDelete = $this->getIdsForNode($id);
			$success = $this->deleteAll(
				[$this->alias . '.id' => $_idsToDelete],
				true,
				true
			);

			if ($success):
				if ($this->isRoot($entry)) {
					$this->Category->id = $entry['Entry']['category_id'];
					$this->Category->updateThreadCounter();
					$this->Esevent->deleteSubject($id, 'thread');
				}
				foreach ($_idsToDelete as $_entryId) {
					$this->Esevent->deleteSubject($_entryId, 'entry');
				}

				$this->_dispatchEvent(
					'Model.Thread.change',
					['subject' => $entry[$this->alias]['tid']]
				);
			endif;

			return $success;
		}

/**
 * Get the ID of all subentries of and including entry $id
 *
 * @param int $id
 * @return array Ids
 */
		public function getIdsForNode($id) {
			$subthread = $this->treeForNode($id);
			$func = function (&$tree, &$entry) {
				$tree['ids'][] = (int)$entry['Entry']['id'];
			};
			Entry::mapTreeElements($subthread, $func);
			sort($subthread['ids']);

			return $subthread['ids'];
		}

/**
 * Anonymizes the entries for a user
 *
 * @param string $userId
 *
 * @return bool success
 */
		public function anonymizeEntriesFromUser($userId) {
			// remove username from all entries and reassign to anonyme user
			$success = $this->updateAll(
				[
					'Entry.name' => "NULL",
					'Entry.edited_by' => "NULL",
					'Entry.ip' => "NULL",
					'Entry.user_id' => 0,
				],
				['Entry.user_id' => $userId]
			);

			if ($success) {
				$this->_dispatchEvent('Cmd.Cache.clear', ['cache' => 'Thread']);
			}

			return $success;
		}

/**
 * Maps all elements in $tree to function $func
 *
 * @param          $leafs
 * @param callable $func
 * @param null     $context
 * @param null     $tree
 *
 * @return string
 */
		public static function mapTreeElements(&$leafs, callable $func, $context = null, &$tree = null) {
			if ($tree === null) {
				$tree = & $leafs;
			}
			foreach ($leafs as $leaf):
				$result = $func($tree, $leaf, $context);
				if ($result === 'break') {
					return 'break';
				}
				if (isset($leaf['_children'])) {
					$result = self::mapTreeElements($leaf['_children'], $func, $context, $tree);
					if ($result === 'break') {
						return 'break';
					}
				}
			endforeach;
		}

/**
 * Merge thread on to entry $targetId
 *
 * @param int $targetId id of the entry the thread should be appended to
 * @return bool true if merge was successfull false otherwise
 */
		public function threadMerge($targetId) {
			$threadIdSource = $this->id;

			$this->contain();
			$sourceEntry = $this->findById($threadIdSource);

			// check that source is thread and not an entry
			if ($sourceEntry[$this->alias]['pid'] != 0) {
				return false;
			}

			$this->contain();
			$targetEntry = $this->findById($targetId);

			// check that target exists
			if (!$targetEntry) {
				return false;
			}

			// check that a thread is not merged onto itself
			if ($targetEntry[$this->alias]['tid'] === $sourceEntry[$this->alias]['tid']) {
				return false;
			}

			// set target entry as new parent entry
			$this->data = [];
			$this->set('pid', $targetEntry[$this->alias]['id']);
			if ($this->save()) {
				// associate all entries in source thread to target thread
				$this->updateAll(
					['tid' => $targetEntry[$this->alias]['tid']],
					['tid' => $this->id]
				);

				// appended source entries get category of target thread
				$this->_threadChangeCategory(
					$targetEntry[$this->alias]['tid'],
					$targetEntry[$this->alias]['category_id']
				);

				// update target thread last answer if source is newer
				$sourceLastAnswer = $this->field('last_answer');
				if (strtotime($sourceLastAnswer) > strtotime($targetEntry[$this->alias]['last_answer'])) {
					$this->id = $targetEntry[$this->alias]['tid'];
					$this->set('last_answer', $sourceLastAnswer);
					$this->save();
				}

				// propagate pinned property from target to source
				$isTargetPinned = (bool)$targetEntry[$this->alias]['locked'];
				if ($sourceEntry[$this->alias]['locked'] !== $isTargetPinned) {
					$this->id = $targetEntry[$this->alias]['tid'];
					$this->_threadLock($isTargetPinned);
				}

				$this->Esevent->transferSubjectForEventType(
					$threadIdSource,
					$targetEntry[$this->alias]['tid'],
					'thread'
				);
				$this->_dispatchEvent(
					'Model.Thread.change',
					['subject' => $targetEntry[$this->alias]['tid']]
				);
				return true;
			}
			return false;
		}

/**
 * Test if entry is thread-root
 *
 * $id accepts an entry-id or an entry: array('Entry' => array(…))
 *
 * @param null $id
 *
 * @return mixed
 * @throws InvalidArgumentException
 */
		public function isRoot($id = null) {
			if ($id === null) {
				$id = $this->id;
			}

			$md5 = md5(serialize($id));
			if (isset($this->_isRoot[$md5]) === false) {
				// $id was $entry array
				if (is_array($id) && isset($id[$this->alias]['pid'])) {
					$entry = $id;
				} else {
					if (is_array($id) && isset($id[$this->alias]['id'])) {
						$id = $id[$this->alias]['id'];
					} elseif (empty($id)) {
						throw new InvalidArgumentException();
					}
					$entry = $this->find(
						'first',
						array(
							'contain' => false,
							'conditions' => array(
								'id' => $id,
							)
						)
					);
				}
				$this->_isRoot[$md5] = empty($entry[$this->alias]['pid']);
			}
			return $this->_isRoot[$md5];
		}

/**
 * Preprocesses entry data before saving it
 *
 * @param       $data
 * @param array $options
 *
 * @throws InvalidArgumentException
 * @throws ForbiddenException
 */
		public function prepare(&$data, array $options = []) {
			$options += [
				'isRoot' => null
			];

			if (isset($options['preFilterFields'])) {
				$org = $data;
				$this->filterFields($data, $options['preFilterFields']);
				if (isset($org['Event'])) {
					$data['Event'] = $org['Event'];
				}
			}
			unset($options['preFilterFields']);

			$isRoot = $options['isRoot'];
			unset($options['isRoot']);

			if ($isRoot === null) {
				$isRoot = $this->isRoot($data);
			}
			// adds info from parent entry to an answer
			if ($isRoot === false) {
				if (!isset($data[$this->alias]['pid'])) {
					$pid = $this->getParentId($data[$this->alias]['id']);
				} else {
					$pid = $data[$this->alias]['pid'];
				}
				$parent = $this->get($pid, true);
				if ($parent === false) {
					throw new InvalidArgumentException;
				}

				// @todo highly @bogus should be a validator?
				$parentPosting = $this->dic->newInstance('\Saito\Posting\Posting', ['rawData' => $parent]);
				if ($parentPosting->isAnsweringForbidden()) {
					throw new ForbiddenException;
				}

				// if new subject is empty use the parent's subject
				if (empty($data[$this->alias]['subject'])) {
					$data[$this->alias]['subject'] = $parent[$this->alias]['subject'];
				}

				$data[$this->alias]['tid'] = $parent[$this->alias]['tid'];
				$data[$this->alias]['category_id'] = $parent[$this->alias]['category_id'];
			}

			//= markup preprocessing
			if (empty($data[$this->alias]['text']) === false) {
				$data[$this->alias]['text'] = $this->prepareMarkup($data[$this->alias]['text']);
			}
		}

		protected function _findEntry($state, $query, $results = []) {
			if ($state === 'before') {
				$query['contain'] = ['User', 'Category'];
				$query['fields'] = array_merge($this->threadLineFieldList, $this->showEntryFieldListAdditional);
				return $query;
			}
			if ($results) {
				return $results[0];
			}
			return $results;
		}

/**
 * Implements the custom find type 'feed'
 *
 * Add parameters for generating a rss/json-feed with find('feed', …)
 */
		protected function _findFeed($state, $query, $results = array()) {
			if ($state == 'before') {
				$query['contain'] = array('User');
				$query['fields'] = $this->_allowedPublicOutputFields;
				$query['limit'] = 10;
				return $query;
			}
			return $results;
		}

/**
 * Locks or unlocks a whole thread
 *
 * Every entry in thread is set to `locked` = '$value'
 *
 * @param bool $value
 */
		protected function _threadLock($value) {
			$tid = $this->field('tid');
			$this->contain();
			// @bogus throws error on $value = false
			$value = $value ? 1 : 0;
			$this->updateAll(['locked' => $value], ['tid' => $tid]);
		}

		public function paginateCount($conditions, $recursive, $extra) {
			if (isset($extra['getInitialThreads'])) {
				$this->Category->contain();
				$categories = $this->Category->find(
					'all',
					[
						'conditions' => array('id' => $conditions['Entry.category_id']),
						'fields' => array('thread_count')
					]
				);
				$count = array_sum(Set::extract('/Category/thread_count', $categories));
			} else {
				$parameters = array('conditions' => $conditions);
				if ($recursive != $this->recursive) {
					$parameters['recursive'] = $recursive;
				}
				$count = $this->find('count', array_merge($parameters, $extra));
			}
			return $count;
		}

		public function beforeSave($options = array()) {
			$success = true;

			//# change category of thread if category of root entry changed
			$modified = !empty($this->id);
			if (isset($this->data[$this->alias]['category_id']) && $modified) {
				$oldEntry = $this->find('first',
					['contain' => false, 'conditions' => ['Entry.id' => $this->id]]);

				if ($oldEntry && (int)$oldEntry[$this->alias]['pid'] === 0) {
					$categoryChanged = (int)$this->data[$this->alias]['category_id'] !== (int)$oldEntry[$this->alias]['category_id'];
					if ($categoryChanged) {
						$success = $success && $this->_threadChangeCategory(
								$oldEntry[$this->alias]['tid'],
								$this->data[$this->alias]['category_id']
							);
					}
				}
			}

			return $success && parent::beforeSave($options);
		}

/**
 * check that entries are only in existing and allowed categories
 *
 * @param $check
 * @return bool
 */
		public function validateCategoryIsAllowed($check) {
			$availableCategories = $this->CurrentUser->Categories->getAllowed();
			if (!isset($availableCategories[$check['category_id']])) {
				return false;
			}
			return true;
		}

		/**
		 * @param $check
		 * @return bool
		 * @throws Exception
		 */
		public function validateEditingAllowed($check) {
			$id = $this->data[$this->alias]['id'];
			$entry = $this->get($id);
			if (empty($entry)) {
				throw new Exception(sprintf('Entry %s not found.', $entry));
			}

			$posting = $this->dic->newInstance('\Saito\Posting\Posting', ['rawData' => $entry]);
			$forbidden = $posting->isEditingAsCurrentUserForbidden();

			if (is_bool($forbidden)) {
				return !$forbidden;
			} else {
				return $forbidden;
			}
		}

/**
 *
 *
 * Don't use Cake's build in maxLength. Dynamically setting the length
 * afterwards in $this->validates it is a bag of hurt with race
 * conditions in ModelValidator::_parseRules() when checking
 * if ($this->_validate === $this->_model->validate) is true.
 *
 * @param $check
 * @return bool
 */
		public function validateSubjectMaxLength($check) {
			return mb_strlen($check['subject']) <= $this->_setting('subject_maxlength');
		}

/**
 * Changes the category of a thread.
 *
 * Assigns the new category-id to all postings in that thread.
 *
 * @param null $tid
 * @param null $newCategoryId
 *
 * @return bool
 * @throws NotFoundException
 * @throws InvalidArgumentException
 */
		protected function _threadChangeCategory($tid = null, $newCategoryId = null) {
			if (empty($tid)) {
				throw new InvalidArgumentException;
			}
			$this->Category->contain();
			$categoryExists = $this->Category->findById($newCategoryId);
			if (!$categoryExists) {
				throw new NotFoundException;
			}
			$out = $this->updateAll(
				['Entry.category_id' => $newCategoryId],
				['Entry.tid' => $tid]
			);
			return $out;
		}

	}