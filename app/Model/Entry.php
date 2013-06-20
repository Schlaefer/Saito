<?php

  App::uses('AppModel', 'Model');
	App::uses('CakeEvent', 'Event');

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

		public $actsAs = array(
			'Containable',
			'Search.Searchable',
			'Tree',
		);

		public $findMethods = array(
			'feed'  => true,
			'entry' => true
		);

		/**
		 * Fields for search plugin
		 *
		 * @var array
		 */
		public $filterArgs = array(
			array('name' => 'subject', 'type' => 'like'),
			array('name' => 'text', 'type' => 'like'),
			array('name' => 'name', 'type' => 'like'),
			array('name' => 'category', 'type' => 'int'),
		);

		public $belongsTo = array(
			'Category' => array(
				'className'  => 'Category',
				'foreignKey' => 'category',
			),
			'User'     => array(
				'className'    => 'User',
				'foreignKey'   => 'user_id',
				'counterCache' => true,
			),
		);

		public $hasMany = array(
			'Bookmark' => array(
				'foreignKey' => 'entry_id',
				'dependent'  => true,
			),
			'Esevent'  => array(
				'foreignKey' => 'subject',
				'conditions' => array('Esevent.subject' => 'Entry.id'),
			),
		);

		public $validate = array(
			'subject'  => array(
				'notEmpty'  => array(
					'rule' => 'notEmpty',
				),
				'maxLength' => array(
					// set to Saito admin pref in beforeValidate()
					'rule' => array('maxLength', 100),
				),
			),
			'category' => array(
				'notEmpty' => array(
					'rule' => 'notEmpty',
					'last' => true,
				),
				'numeric'  => array(
					'rule' => 'numeric'
				),
			),
			'user_id'  => array(
				'rule' => 'numeric'
			),
			'views'    => array(
				'rule' => array('comparison', '>=', 0),
			),
			'name'     => array(),
		);

		protected $fieldsToSanitize = array(
			'subject',
			'text',
		);

		/**
		 * Fields allowed in public output
		 *
		 * @var array
		 */
		public $publicFieldsList = '
			Entry.id,
			Entry.pid,
			Entry.tid,
			Entry.time,
			Entry.last_answer,
			Entry.edited,
			Entry.edited_by,
			Entry.user_id,
			Entry.name,
			Entry.subject,
			Entry.category,
			Entry.text,
			Entry.locked,
			Entry.fixed,
			Entry.views,
			Entry.nsfw,
			User.username
		';

		/**
		 * field list necessary for displaying a thread_line
		 *
		 * Entry.text determine if Entry is n/t
		 *
		 * @var string
		 */
		public $threadLineFieldList = '
			Entry.id,
			Entry.pid,
			Entry.tid,
			Entry.subject,
			Entry.text,
			Entry.time,
			Entry.fixed,
			Entry.last_answer,
			Entry.views,
			Entry.user_id,
			Entry.locked,
			Entry.flattr,
			Entry.nsfw,
			Entry.name,

			User.username,

			Category.accession,
			Category.category,
			Category.description
		';

		/**
		 * fields additional to $threadLineFieldList to show complete entry
		 *
		 * @var string
		 */
		public $showEntryFieldListAdditional = '
			Entry.edited,
			Entry.edited_by,
			Entry.ip,
			Entry.category,

			User.id,
			User.flattr_uid,
			User.signature,
			User.user_place
		';

		public function getRecentEntries(array $options = array(), SaitoUser $User) {
			Stopwatch::start('Model->User->getRecentEntries()');

			$options += [
				'user_id' => null,
				'limit' => 10,
				'category' => $this->Category->getCategoriesForAccession(
					$User->getMaxAccession()
				),
			];

			$cache_key = 'Entry.recentEntries-' . md5(serialize($options));
			$cached_entry = Cache::read($cache_key, 'entries');
			if ($cached_entry) {
				Stopwatch::stop('Model->User->getRecentEntries()');
				return $cached_entry;
			}

			$conditions = array();
			if ($options['user_id'] !== null) {
				$conditions[]['Entry.user_id'] = $options['user_id'];
			}
			if ($options['category'] !== null):
				$conditions[]['Entry.category'] = $options['category'];
			endif;

			$result = $this->find('all',
					array(
						'contain' => array('User', 'Category'),
						'fields'		 => $this->threadLineFieldList,
						'conditions' => $conditions,
						'limit'			 => $options['limit'],
						'order'			 => 'time DESC',
					)
			);

			Cache::write($cache_key, $result, 'entries');

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
			$entry = $this->find('first', array(
					'contain' => false,
					'conditions' => array(
							'Entry.id' => $id,
					),
					'fields' => 'Entry.tid'

			));
			if ($entry == false) {
				throw new UnexpectedValueException ('Posting not found. Posting-Id: ' . $id);
			}
			return $entry['Entry']['tid'];
		}

	/**
	 * creates a new root or child entry for a node
	 *
	 * Interface see model->save()
	 */
	public function createPosting($data = null, $validate = true, $fieldList = array()) {

		if (	 !isset($data['Entry']['pid'])
				|| !isset($data['Entry']['subject'])
				|| !isset($data['Entry']['category'])) {
			return false;
		}

		$isNewThread = (int)$data['Entry']['pid'] === 0;

		if ($isNewThread === false) {
			// reply: setup additional data from parent entry
			$this->contain();
			$parent_entry = $this->read(array('tid', 'category'), $data['Entry']['pid']);
			if ($parent_entry === false) { return false; }
			$data['Entry']['tid']      = $parent_entry['Entry']['tid'];
			$data['Entry']['category'] = $parent_entry['Entry']['category'];
		}

		$data['Entry']['time']        = date("Y-m-d H:i:s");
		$data['Entry']['last_answer'] = date("Y-m-d H:i:s");
		$data['Entry']['ip']          = self::_getIp();

		$this->create();
		$new_posting = $this->save($data, $validate,$fieldList);

		if ($new_posting === false) { return false; }
		$new_posting_id	= $this->id;
		if ($new_posting === true) { $new_posting = $this->read(); }

		if($isNewThread) {
			// thread-id of new thread is its own id
			$new_posting['Entry']['tid'] = $new_posting_id;
			if ($this->save($new_posting) === false) {
				// @td raise error and/or roll back new entry
				return false;
			} else {
        $this->Category->id = $data['Entry']['category'];
        $this->Category->updateThreadCounter();
      }
		} else  {
			// update last answer time of root entry
			$this->id = $parent_entry['Entry']['tid'];
			$this->set('last_answer', $new_posting['Entry']['last_answer']);
			if ($this->save() === false) {
				// @td raise error and/or roll back new entry
				return false;
			}

			$this->getEventManager()->dispatch(
				new CakeEvent(
					'Model.Entry.replyToEntry',
					$this,
					array(
						'subject' => $new_posting['Entry']['pid'],
						'data'    => $new_posting,
					)
				)
			);
			$this->getEventManager()->dispatch(
				new CakeEvent(
					'Model.Entry.replyToThread',
					$this,
					array(
						'subject' => $new_posting['Entry']['tid'],
						'data'    => $new_posting,
					)
				)
			);
		}
		$this->id = $new_posting_id;
		return $new_posting;
	}

	/* @mb `views` into extra related table if performance becomes a problem */
	public function incrementViews($amount=1) {
		// Workaround for travis-ci error message
		// @see https://travis-ci.org/Schlaefer/Saito/builds/3196834
		if (!env('TRAVIS')) {
			$this->contain();
			$this->saveField('views', $this->field('views') + $amount);
		}
	}

		/**
		 * tree of a single node and its subentries
		 *
		 * $options = array(
		 *    'root' => true // performance improvements if it's a known thread-root
		 *    'complete' => true // include all fields necessary to render the complete entries
		 * );
		 *
		 * @param int $id
		 * @param array $options
		 * @return array tree
		 */
		public function treeForNode($id, $options = array()) {
			$options += [
				'root'     => false,
				'complete' => false
			];

			if ($options['root']) {
				$tid = $id;
			} else {
				$tid = $this->getThreadId($id);
			}

			$fields = null;
			if ($options['complete']) {
				$fields = $this->threadLineFieldList . ',' . $this->showEntryFieldListAdditional;
			}

			$tree = $this->treesForThreads([['id' => $tid]], null, $fields);

			if ((int)$tid !== (int)$id) {
				$tree = $this->treeGetSubtree($tree, $id);
			}

			if ($options['complete'] && $tree) {
				$this->_addAdditionalFields($tree);
			}

			return $tree;
		}

		/**
		 * trees for multiple tids
		 */
		public function treesForThreads($search_array, $order = null, $fieldlist = null) {
			if (empty($search_array)) {
				return [];
			}

			Stopwatch::start('Model->Entries->treeForNodes() DB');

			if (empty($order)) {
				$order = 'last_answer ASC';
			}

			$where = [];
			foreach ($search_array as $search_item) {
				$where[] = $search_item['id'];
			}

			if ($fieldlist === null) {
				$fieldlist = $this->threadLineFieldList;
			}

			$threads = $this->_getThreadEntries(
				$where,
				[
					'order' => $order,
					'fields' => $fieldlist
				]
			);
			Stopwatch::stop('Model->Entries->treeForNodes() DB');

			$out = false;
			if ($threads) {
				Stopwatch::start('Model->Entries->treeForNodes() CPU');
				$out = $this->treeBuild($threads);
				Stopwatch::stop('Model->Entries->treeForNodes() CPU');
			}

			return $out;
		}

		/**
		 *
		 * @param mixed thread-ids, one int or many array
		 * @param array optional $params
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
				'order'  => 'last_answer ASC'
			];
			$threads = $this->find(
				'all',
				[
					'conditions' => ['tid' => $tid],
					'contain'    => ['User', 'Category'],
					'fields'     => $params['fields'],
					'order'      => $params['order']
				]
			);

			return $threads;
		}

		public function toggle($key) {
			$result = parent::toggle($key);

			if ($key === 'locked') {
				$this->_threadLock($result);
			}

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

			$this->validate['subject']['maxLength']['rule'][1] = Configure::read(
				'Saito.Settings.subject_maxlength'
			);

			//* in n/t posting delete unnecessary body text
			if (isset($this->data['Entry']['text'])) {
				$this->data['Entry']['text'] = rtrim($this->data['Entry']['text']);
			}
		}

		/**
		 * Deletes entry and all it's subentries and associated data
		 *
		 * @param type $id
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

			$ids_to_delete = $this->getIdsForNode($id);
			$success       = $this->deleteAll(
				['Entry.id' => $ids_to_delete],
				true,
				true
			);

			if ($success):
				if ($this->isRoot($entry)) {
					$this->Category->id = $entry['Entry']['category'];
					$this->Category->updateThreadCounter();
					$this->Esevent->deleteSubject($id, 'thread');
				}
				foreach ($ids_to_delete as $entry_id) {
					$this->Esevent->deleteSubject($entry_id, 'entry');
				}
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
			$func      = function (&$tree, &$entry) {
				$tree['ids'][] = (int)$entry['Entry']['id'];
			};
			Entry::mapTreeElements($subthread, $func);
			sort($subthread['ids']);

			return $subthread['ids'];
		}

		/**
		 * Anonymizes the entries for a user
		 *
		 * @param string $user_id
		 * @return bool success
		 */
		public function anonymizeEntriesFromUser($user_id) {

			// remove username from all entries and reassign to anonyme user
			return $this->updateAll(
				[
					'Entry.name'      => "NULL",
					'Entry.edited_by' => "NULL",
					'Entry.ip'        => "NULL",
					'Entry.user_id'   => 0,
				],
				['Entry.user_id' => $user_id]
			);
		}

		/**
		 * Maps all elements in $tree to function $func
		 *
		 * @param type $leafs Current subtree.
		 * @param function $func Function to execute.
		 * @param misc $context Arbitrary data for the function. Useful for providing $this context.
		 * @param array $tree The whole tree.
		 */
		public static function mapTreeElements(&$leafs, $func, $context = null, &$tree = null) {
			if ($tree === null) {
				$tree = & $leafs;
			}
			foreach ($leafs as &$leaf):
				$result = $func($tree, $leaf, $context);
				if ($result === 'break')
					return 'break';
				if (isset($leaf['_children'])):
					$result = self::mapTreeElements($leaf['_children'], $func, $context, $tree);
					if ($result === 'break')
						return 'break';
				endif;
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
			if ($sourceEntry['Entry']['pid'] != 0) {
				return false;
			}

			$this->contain();
			$targetEntry = $this->findById($targetId);

			// check that target exists
			if (!$targetEntry) {
				return false;
			}

			// check that a thread is not merged onto itself
			if ($targetEntry['Entry']['tid'] === $sourceEntry['Entry']['tid']) {
				return false;
			}

			// set target entry as new parent entry
			$this->set('pid', $targetEntry['Entry']['id']);
			if ($this->save()) {
				// associate all entries in source thread to target thread
				$this->updateAll(
					['tid' => $targetEntry['Entry']['tid']],
					['tid' => $this->id]
				);

				// appended source entries get category of target thread
				$this->_threadChangeCategory(
					$targetEntry['Entry']['tid'],
					$targetEntry['Entry']['category']
				);

				// update target thread last answer if source is newer
				$sourceLastAnswer = $this->field('last_answer');
				if (strtotime($sourceLastAnswer) > strtotime($targetEntry['Entry']['last_answer'])) {
					$this->id = $targetEntry['Entry']['tid'];
					$this->set('last_answer', $sourceLastAnswer);
					$this->save();
				}

				$this->Esevent->transferSubjectForEventType(
					$threadIdSource,
					$targetEntry['Entry']['tid'],
					'thread'
				);

				return true;
			}

			return false;
		}

		protected function _addAdditionalFields(&$entries) {
			/**
			 * Function for checking if entry is bookmarked by current user
			 *
			 * @var function
			 */
			$ldGetBookmarkForEntryAndUser = function (&$tree, &$element, $_this) {
					$bookmarks = $this->_CurrentUser->getBookmarks();
					$element['isBookmarked'] = isset($bookmarks[$element['Entry']['id']]);
			};
			Entry::mapTreeElements($entries, $ldGetBookmarkForEntryAndUser, $this);

			/**
			 * Function for checking user rights on an entry
			 *
			 * @var function
			 */
			$ldGetRightsForEntryAndUser = function (&$tree, &$element, $_this) {
				$rights = [
					'isEditingForbidden' => $_this->isEditingForbidden($element, $_this->_CurrentUser),
					'isEditingAsUserForbidden' => $_this->isEditingForbidden($element, $_this->_CurrentUser->mockUserType('user')),
					'isAnsweringForbidden' => $_this->isAnsweringForbidden($element)
				];
				$element['rights'] = $rights;
			};
			Entry::mapTreeElements($entries, $ldGetRightsForEntryAndUser, $this);
		}

		/**
		 * Check if someone is allowed to edit an entry
		 *
		 * @param array $entry
		 * @param SaitoUser $user
		 * @return boolean
		 */
		public function isEditingForbidden(array $entry, SaitoUser $User) {
			// user is not logged in and not allowed to do anything
			if (empty($User) || empty($User['id']))
				return true;

			$verboten = true;

			// Mod and Admin …
			# @td mods don't edit admin posts
			if ($User->isMod() || $User->isAdmin()) {
				if (
						(int)$User['id'] === (int)$entry['Entry']['user_id']
						&& ( time() > strtotime($entry['Entry']['time']) + ( Configure::read('Saito.Settings.edit_period') * 60 ))
						/* Mods should be able to edit their own posts if they are pinned
						 *
						 * @td this opens a 'mod can pin and then edit root entries'-loophole,
						 * as long as no one checks pinning for Configure::read('Saito.Settings.edit_period') * 60
						 * for mods pinning root-posts.
						 */
						&& ( $entry['Entry']['fixed'] == false )
						&& ( $User->isModOnly() )
				) :
					// mods shouldn't mod themselfs
					$verboten = 'time';
				else :
					$verboten = false;
				endif;

				// Normal user and anonymous
			} else {
				// check if it's users own posting @td put admin and mods here;
				if ($User['id'] != $entry['Entry']['user_id']) {
					$verboten = 'user';
				}
				// check if time for editint ran out
				elseif (time() > strtotime($entry['Entry']['time']) + ( Configure::read('Saito.Settings.edit_period') * 60 )) {
					$verboten = 'time';
					// entry is locked by admin or mod
				} elseif ($this->_isLocked($entry)) {
					$verboten = 'locked';
				} else {
					$verboten = false;
				}
			}

			return $verboten;
		}

		/**
		 * Test if entry is thread-root
		 *
		 * $id accepts an entry-id or an entry: array('Entry' => array(…))
		 *
		 * @param mixed $id
		 * @return bool
		 */
		public function isRoot($id = null) {
			if ($id === null) {
				$id = $this->id;
			}

			if (is_array($id) && isset($id[$this->alias]['pid'])) {
				$entry = $id;
			}
			elseif (isset($this->data[$this->alias]['id']) && (int)$this->data[$this->alias]['id'] === (int)$id && isset($this->data[$this->alias]['pid'])) {
				$entry = $this->data;
			} else {
				$entry = $this->find('first', array(
								'contain' => false,
								'conditions' => array(
										'id' => $id,
								)
						));
			}
			return (empty($entry[$this->alias]['pid']));
		}

		protected function _isLocked($entry) {
			if (!isset($entry['Entry']['locked'])) {
				throw new InvalidArgumentException;
			}

			return $entry['Entry']['locked'] != false;
		}

		public function getUnsanitized($id) {
				$this->sanitize(false);
				return $this->find('entry', array('conditions' => array('Entry.id' => $id)));
		}

		public function _findEntry($state, $query, $results = array()) {
			if ($state === 'before') {
				$query['contain'] = array('User', 'Category');
				$query['fields'] = $this->threadLineFieldList . ',' . $this->showEntryFieldListAdditional;
				return $query;
			}
			if ($results) {
				$this->_addAdditionalFields($results);
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
				$query['contain']	 = array('User');
				$query['fields']   = $this->publicFieldsList;
				$query['limit']		 = 10;
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
		$this->updateAll(
				array('locked' => $value),
				array('tid'	=> $tid)
		);
	}

	/**
	 * Checks if answering an entry is allowed
	 *
	 * @param array $entry
	 * @return boolean
	 */
	public function isAnsweringForbidden($entry) {
		$isAnsweringForbidden = true;
		if ($this->_isLocked($entry)) {
			$isAnsweringForbidden = 'locked';
		} else {
			$isAnsweringForbidden = false;
		}

		return $isAnsweringForbidden;
	}


  public function paginateCount($conditions, $recursive, $extra) {

    if ( isset($extra['getInitialThreads']) ):
        $this->Category->contain();
        $categories = $this->Category->find('all',
            array(
            'conditions' => array( 'id' => $conditions['Entry.category'] ),
            'fields' => array( 'thread_count' )
                ));
        $count = array_sum(Set::extract('/Category/thread_count', $categories));
      else:
        $parameters = array( 'conditions' => $conditions );
        if ( $recursive != $this->recursive ) {
          $parameters['recursive'] = $recursive;
        }
        $count = $this->find('count', array_merge($parameters, $extra));
      endif;

    return $count;
  }

		public function beforeSave($options = array()) {
			$success = true;

			// change category of thread if category of root entry changed
			if (isset($this->data['Entry']['category'])) {
				// get old entry to compare with new data
				$old_entry = $this->find(
					'first',
					array(
						'contain'    => false,
						'conditions' => array(
							'Entry.id' => $this->id,
						),
					)
				);

				if ($old_entry && (int)$old_entry['Entry']['pid'] === 0) {
					if ((int)$this->data['Entry']['category'] !== (int)$old_entry['Entry']['category']) {
						$success = $success && $this->_threadChangeCategory(
							$old_entry['Entry']['tid'],
							$this->data['Entry']['category']
						);
					}
				}
			}

			return $success && parent::beforeSave($options);
		}

	/**
	 * Changes the category of a thread.
	 *
	 * Assigns the new category-id to all postings in that thread.
	 *
	 * @param int $tid Id of the thread
	 * @param int $new_category_id Id of the new category
	 * @return boolean True on success, false on failure
	 */
	protected function _threadChangeCategory($tid = null, $new_category_id = null) {
		if (empty($tid)) {
			throw new InvalidArgumentException;
		}
		$this->Category->contain();
		$category_exists = $this->Category->findById($new_category_id);
		if (!$category_exists) {
			throw new NotFoundException;
		}
		$out = $this->updateAll(
				array(
						'Entry.category' => $new_category_id,
				),
				array(
						'Entry.tid' => $tid,
		));
		return $out;
	}
}