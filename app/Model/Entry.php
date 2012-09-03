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
		);

		public $findMethods = array(
				'feed'	 => true,
				'entry'	 => true,
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
		);

		public $belongsTo = array(
				'Category' => array(
						'className'	 => 'Category',
						'foreignKey' => 'category',
				),
				'User'			 => array(
						'className'		 => 'User',
						'foreignKey'	 => 'user_id',
						'counterCache' => true,
				),
		);

		public $hasMany = array(
				'Bookmark' => array(
						'foreignKey' => 'entry_id',
						'dependent'	 => true,
				),
				'Esevent'		 => array(
						'foreignKey' => 'subject',
						'conditions' => array('Esevent.subject' => 'Entry.id'),
				),
		);

	public $validate = array(
				'subject' => array(
						'notEmpty' => array(
								'rule'			 => 'notEmpty',
						),
						'maxLength'	 => array(
								// set to Saito admin pref in beforeValidate()
								'rule' => array('maxLength', 100),
						),
				),
				'category' => array(
						'notEmpty' => array(
								'rule'		 => 'notEmpty',
								'last'		 => true,
						),
						'numeric'	 => array(
								'rule'		 => 'numeric'
						),
				),
				'user_id'	 => array(
						'rule'	 => 'numeric'
				),
				'views'	 => array(
						'rule' => array('comparison', '>=', 0),
				),
				'name' => array(),
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
		 * @var type string
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

			User.id,
			User.flattr_uid,
			User.signature,
			User.user_place
		';

		public function getRecentEntries(array $options = array(), SaitoUser $User) {
			Stopwatch::start('Model->User->getRecentEntries()');

			$defaults = array(
					'user_id'	 => NULL,
					'limit'		 => 10,
					'category' => $this->Category->getCategoriesForAccession($User->getMaxAccession()),
			);
			$options = array_merge($defaults, $options);
			extract($options);

			$cache_key = 'Entry.recentEntries-' . md5(serialize($options));
			$cached_entry = Cache::read($cache_key, 'postings');
			if ($cached_entry) {
				Stopwatch::stop('Model->User->getRecentEntries()');
				return $cached_entry;
			}

			$conditions = array();
			if ($user_id !== NULL) {
				$conditions[]['Entry.user_id'] = $user_id;
			}
			if ($category !== NULL):
				$conditions[]['Entry.category'] = $category;
			endif;

			$result = $this->find('all',
					array(
						'contain' => array('User', 'Category'),
						'fields'		 => $this->threadLineFieldList,
						'conditions' => $conditions,
						'limit'			 => $limit,
						'order'			 => 'time DESC',
					)
			);

			Cache::write($cache_key, $result, 'postings');

			Stopwatch::stop('Model->User->getRecentEntries()');
			return $result;
		}

	/**
	 * creates a new root or child entry for a node 
	 * 
	 * Interface see model->save()
	 */
	public function createPosting($data = null, $validate = true, $fieldList = array()) {

		if ( !isset($data['Entry']['pid']) || !isset($data['Entry']['subject']) || !isset($data['Entry']['category']) ) {
			return FALSE;
		}

		if ($data['Entry']['pid'] > 0) {	
			// reply
			// get and setup additional data from parent entry

			$this->id 		= $data['Entry']['pid'];
			$this->contain();
			$parent_entry = $this->read(array('tid', 'category'));
			if ( $parent_entry != TRUE ) {
				//* parent could not be found 
				return FALSE;
				}

			//* update new entry with thread data
			$data['Entry']['tid'] 				= $parent_entry['Entry']['tid'];
			$data['Entry']['category'] 		= $parent_entry['Entry']['category'];
		}

		$data['Entry']['time']				= date("Y-m-d H:i:s");
		$data['Entry']['last_answer'] = date("Y-m-d H:i:s");
    $data['Entry']['ip']          = self::_getIp();

		$this->create();
		$new_posting = $this->save($data, $validate,$fieldList);

		if ( $new_posting != TRUE ) {
			return $new_posting;
			}

		if ( $new_posting === TRUE ) {
			$new_posting = $this->read(null, $this->id);
		}

		$new_posting_id	= $this->id;

		if((int)$new_posting['Entry']['pid'] === 0) {
			// new thread

			// for new thread tid = id 
			$new_posting['Entry']['tid'] = $new_posting_id;

			if ($this->save($new_posting) != TRUE ) {
				// @td raise error and/or roll back new entry
				return FALSE;
			} else {
        $this->Category->id = $data['Entry']['category'];
        $this->Category->updateThreadCounter();
      }

		} elseif ($new_posting['Entry']['pid'] > 0) {	
			// reply
			
			// update last answer time in root entry
			$this->id = $parent_entry['Entry']['tid'];
			$this->read();
			$this->set('last_answer', $new_posting['Entry']['last_answer']);
			if ( $this->save() != TRUE ) {
				// @td raise error and/or roll back new entry
				return FALSE;
				}

			$this->getEventManager()->dispatch(
					new CakeEvent(
							'Model.Entry.replyToEntry',
							$this,
							array(
									'subject'	=> $new_posting['Entry']['pid'],
									'data' => $new_posting,
									)
							)
					);
			$this->getEventManager()->dispatch(
					new CakeEvent(
							'Model.Entry.replyToThread',
							$this,
							array(
									'subject'	=> $new_posting['Entry']['tid'],
									'data' => $new_posting,
									)
							)
					);
		}

		$this->id = $new_posting_id;
		return $new_posting;
	}

	/* @mb `views` into extra related table if performance becomes a problem */
	public function incrementViews($amount=1) {
		$this->contain();
		$views = $this->saveField('views', $this->field('views') + $amount);
	}

	public function treeForNode($id, $order = 'last_answer ASC') {
		return $this->treeForNodes(
				array(
						array(
								'id' => $id,
								'tid' => null,
								'pid' => null,
								'last_answer' => null
								)
						),
				$order);
	}

	public function treeForNodesComplete($id, $order = 'last_answer ASC') {
		$result = $this->treeForNodes(
        array(
            array( 'id' => $id, 'tid' => null, 'pid' => null, 'last_answer' => null ) ),
        $order,
        $this->threadLineFieldList . ',' . $this->showEntryFieldListAdditional
        );
		$this->_addAdditionalFields($result);
		return $result;
	}

	public function treeForNodes($search_array, $order = 'last_answer ASC', $fieldlist = NULL) {
		Stopwatch::start('Model->Entries->treeForNodes() DB');

		if (empty($search_array)) {
			Stopwatch::stop('Model->Entries->treeForNodes() DB');
			return array();
		}

		$where = array();
		foreach($search_array as $search_item) {
			$where[] = $search_item['id'];
		}

		if ($fieldlist === NULL) {
      $fieldlist = $this->threadLineFieldList;
		}

		$threads = $this->_getThreadEntries($where, array(
					'order'	 => $order,
					'fields' => $fieldlist,
					));
		Stopwatch::stop('Model->Entries->treeForNodes() DB');

		Stopwatch::start('Model->Entries->treeForNodes() CPU');
		$out = $this->parseTreeInit($threads);
		$out = $this->sortTime($out);
		Stopwatch::stop('Model->Entries->treeForNodes() CPU');

		return $out;
	}

	/**
		 *
		 * @param mixed thread-ids, one int or many array
		 * @param array optional $params
		 *
		 * <pre>
		 * 	array(
		 * 			'fields'		=> array('Entry.id'),
		 * 			'order'			=> 'time ASC',
		 *  )
		 * </pre>
		 */
		protected function _getThreadEntries($tid, array $params = array()) {
			$default = array(
					'order'		 => 'last_answer ASC',
					'fields'	 => $fieldlist = $this->threadLineFieldList,
			);
			$params		 = array_merge($default, $params);
			$threads	 = $this->find('all',
					array(
					'conditions' => array(
							'tid'			 => $tid,
					),
					'contain'	 => array('User', 'Category'),
					'fields' => $params['fields'],
					'order'	 => $params['order'],
					)
			);
			return $threads;
		}

	public function toggle($key) {
		$result = parent::toggle($key);

		if ($key === 'locked') {
			$this->_lockThread($result);
		}

		return $result;
	}

	/*
	 * bread and butter quicksort
	 */
	protected function quicksort($in) {
		if (count($in) < 2)
			return $in;
		$left = $right = array();

		reset($in);
		$pivot_key = key($in);
		$pivot = array_shift($in);

		foreach ($in as $k => $v) {
			if ($v['Entry']['time'] < $pivot['Entry']['time'])
				$left[$k] = $v;
			else
				$right[$k] = $v;
		}
		return array_merge($this->quicksort($left), array($pivot_key => $pivot), $this->quicksort($right));
	}

	protected function sortTime($in, $level = 0) {
		if ($level > 0)
		{
				$in = $this->quicksort($in);
		}
		foreach($in as $k => $v) {
			if (isset($v['_children'])) {
				$in[$k]['_children'] = $this->sortTime($v['_children'], $level+1);
			}

		}
		return $in;
	}

	protected function parseTreeInit($threads) {
		$tree = array();
		foreach ($threads as $thread) { 
			$this->parseTreeRecursive($tree, $thread);
		}
		return $tree[0]['_children'];
	}

	protected function parseTreeRecursive(&$tree, $item) {
    $id = $item[$this->alias]['id'];
    $pid = $item[$this->alias]['pid'];
    $tree[$id] = isset($tree[$id]) ? $item + $tree[$id] : $item;
		$tree[$pid]['_children'][] = &$tree[$id];
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

	 	$this->validate['subject']['maxLength']['rule'][1] = Configure::read('Saito.Settings.subject_maxlength');

	 	//* in n/t posting delete unnecessary body text
	 	if( isset($this->data['Entry']['text']) ) {
			$this->data['Entry']['text'] = rtrim($this->data['Entry']['text']);
			}
	}

	public function deleteTree() {

		// delete only whole trees
		$pid = $this->field('pid');
		if ((int)$pid !== 0) {
		 return false;
		}

    $category 	= $this->field('category');
		$entry_ids 	= Hash::extract($this->_getThreadEntries($this->id), '{n}.Entry.id');

    $success = $this->deleteAll(array('tid' => $this->id), true, true);

    if ($success):
      $this->Category->id = $category;
      $this->Category->updateThreadCounter();
			$this->Esevent->deleteSubject($this->id, 'thread');
			foreach($entry_ids as $entry_id) {
				$this->Esevent->deleteSubject($entry_id, 'entry');
			}
    endif;

		return $success;
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
        array(
            'Entry.name'      => "NULL",
            'Entry.edited_by' => "NULL",
            'Entry.ip'        => "NULL",
            'Entry.user_id'   => 0,
            ),
        array('Entry.user_id' => $user_id)
        );
  }

	/**
	 * Maps all elements in $tree to function $func
	 *
	 * @param type $tree
	 * @param type $_this
	 * @param type $func
	 */
	public static function mapTreeElements(&$tree, $func, $_this = NULL) {
		foreach ($tree as &$leaf ):
			$func(&$leaf, $_this);
			if(isset($leaf['_children'])):
					self::mapTreeElements($leaf['_children'], $func, $_this);
			endif;
		endforeach;
	}

	/**
	 * Merge thread on to entry $targetId
	 *
	 * @param int $targetId id of the entry the thread should be appended to
	 * @return bool true if merge was successfull false otherwise
	 */
	public function merge($targetId) {
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
		if ( $this->save() ) {
			// associate all entries in source thread to target thread
			$this->updateAll(
					array(
							'tid' => $targetEntry['Entry']['tid'],
							),
					array('tid'	=> $this->id)
			);

			// update target thread last answer if source is newer
			$sourceLastAnswer = $this->field('last_answer');
			if (strtotime($sourceLastAnswer) > strtotime($targetEntry['Entry']['last_answer'])) {
				$this->id = $targetEntry['Entry']['tid'];
				$this->set('last_answer', $sourceLastAnswer);
				$this->save();
			}

			$this->Esevent->transferSubjectForEventType($threadIdSource,
					$targetEntry['Entry']['tid'], 'thread');

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
			$ldGetBookmarkForEntryAndUser = function($element, $_this) {
						$element['isBookmarked'] = $_this->Bookmark->isBookmarked(
								$element['Entry']['id'], $_this->_CurrentUser->getId());
					};

			Entry::mapTreeElements($entries, $ldGetBookmarkForEntryAndUser, $this);

			/**
			 * Function for checking user rights on an entry
			 *
			 * @var function
			 */
			$ldGetRightsForEntryAndUser = function($element, $_this) {
						$rights = array(
					'isEditingForbidden' => $_this->isEditingForbidden($element, $_this->_CurrentUser),
					'isEditingAsUserForbidden' => $_this->isEditingForbidden($element, $_this->_CurrentUser->mockUserType('user')),
								'isAnsweringForbidden' => $_this->isAnsweringForbidden($element),
						);
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
						&& ( $entry['Entry']['fixed'] == FALSE )
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

		protected function _isLocked($entry) {
				return $entry['Entry']['locked'] != false;
		}

		public function _findEntry($state, $query, $results = array()) {
			if ($state == 'before') {
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
	protected function _lockThread($value) {
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
	public function isAnsweringForbidden($entry = NULL) {
		$isAnsweringForbidden = true;

		if (!isset($entry['Entry']['locked'])) return true;

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
}