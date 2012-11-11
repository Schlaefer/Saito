<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class EntriesController extends AppController {

	public $name = 'Entries';
	public $helpers = array(
			'EntryH',
      'MarkitupEditor',
			'Flattr.Flattr',
			'Text',
	);
	public $components = array(
			'CacheTree',
			'Flattr',
      // for RSS-feed
      'RequestHandler',
			'Search.Prg',
	);
	/**
	 * Setup for Search Plugin
	 *
	 * @var array
	 */
	public $presetVars = array(
			array( 'field' => 'subject', 'type' => 'value' ),
			array( 'field' => 'text', 'type' => 'value' ),
			array( 'field' => 'name', 'type' => 'value' ),
			array( 'field' => 'category', 'type' => 'value' ),
	);

		public function index() {
			Stopwatch::start('Entries->index()');

			if ( $this->CurrentUser->isLoggedIn() ) {
				// get current user's recent entries for slidetab
				$this->set('recentPosts',
						$this->Entry->getRecentEntries(
								array(
										'user_id' => $this->CurrentUser->getId(),
										'limit' => 5,
								), $this->CurrentUser)
				);

				// get last 10 recent entries for slidetab
				$this->set('recentEntries',
						$this->Entry->getRecentEntries(array(),
								$this->CurrentUser
								));
			}

			// get threads
			extract($this->_getInitialThreads($this->CurrentUser));

			// match threads against cache
			$cachedThreads = array();
			$uncachedThreads = array();
			foreach($initialThreads as $key => $thread) {
				if ($this->CacheTree->isCacheValid($thread)) {
					$cachedThreads[$thread['id']] = $thread;
				} else {
					$uncachedThreads[$thread['id']] = $thread;
				}
			}

			// set cached threads for view
			$this->set('cachedThreads', $cachedThreads);
			 
			// get threads not available in cache
			$dbThreads = $this->Entry->treeForNodes($uncachedThreads, $order);

			$threads = array();
			foreach( $initialThreads as $thread ) {
				if (isset($cachedThreads[$thread['id']])) {
					$threads[]['Entry'] = $cachedThreads[$thread['id']];
					unset($cachedThreads[$thread['id']]);
				} else {
					foreach($dbThreads as $k => $dbThread) {
						if($dbThread['Entry']['tid'] == $thread['id']) {
							$threads[] = $dbThreads[$k];
							unset($dbThreads[$k]);
						}
					}
				}
			}

			$this->set('entries', $threads);

			$currentPage = 1;
			if (isset($this->request->named['page']) && $this->request->named['page'] != 1):
				$currentPage = $this->request->named['page'];
				$this->set('title_for_layout', __('page') . ' ' . $currentPage);
			endif;
			$this->Session->write('paginator.lastPage', $currentPage);

			$this->set('showDisclaimer', TRUE);

			Stopwatch::stop('Entries->index()');
		}

	public function feed() {
			Configure::write('debug', 0);


			if (isset($this->request->params['named']['depth']) && $this->request->params['named']['depth'] === 'start') {
				$title						 = __('Last started threads');
				$order						 = 'time DESC';
				$conditions['pid'] = 0;
			} else {
				$title = __('Last entries');
				$order = 'last_answer DESC';
			}

			$conditions['category'] = $this->Entry->Category->getCategoriesForAccession($this->CurrentUser->getMaxAccession());

			$entries = $this->Entry->find('feed',
					array(
					'conditions' => $conditions,
					'order'			 => $order,
					));
			$this->set('entries', $entries);

			// serialize for JSON
			$this->set('_serialize', 'entries');
			$this->set('title', $title);

			return;
		}

	public function mix($tid) {
		if (!$tid) {
			$this->redirect('/');
		}
		$entries = $this->Entry->treeForNodesComplete($tid);

		if ($entries == false) {
			throw new NotFoundException();
		}

		//* check if anonymous tries to access internal categories
		if ($entries[0]['Category']['accession'] > $this->CurrentUser->getMaxAccession()) {
			return $this->redirect('/');
		}

		$this->set('title_for_layout', $entries[0]['Entry']['subject']);
		$this->set('entries', $entries);
    $this->_showAnsweringPanel();
	}

	# @td MVC user function ?

	public function update() {
		$this->autoRender = false;
		$this->CurrentUser->LastRefresh->forceSet();
		$this->redirect('/entries/index');
	}

	public function noupdate() {
		$this->Session->write('User_last_refresh_disabled', true);
		$this->redirect('/entries/index');
	}

	public function setcategory($id = null) {
		if(!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException();
		}

		if ($id == 'all' || ($this->request->data && $this->request->data['CatMeta']['All'])) {
			// set meta category 'all'
			$this->Entry->User->id = $this->CurrentUser->getId();
			$this->Entry->User->set('user_category_active', -1);
			$this->Entry->User->save();
		} elseif (!$id && $this->request->data) {
			// set custom set
			$this->Entry->User->id = $this->CurrentUser->getId();
			$this->Entry->User->set('user_category_active', 0);
			$this->Entry->User->set('user_category_custom', $this->request->data['CatChooser']);
			$this->Entry->User->save();
		} else {
			// set single category
			$this->Entry->User->id = $this->CurrentUser->getId();
			$this->Entry->User->set('user_category_active', $id);
			$this->Entry->User->save();
		}
		return $this->redirect(array('controller' => 'entries', 'action' => 'index'));
	}

  /**
     * Outputs raw BBcode of an posting $id
     *
     * @param int $id
     * @return string
     */
    public function source($id = NULL) {
      $data = $this->requestAction('/entries/view/' . $id);

      $this->autoLayout = false;
      $this->autoRender = false;

      $out = array( );
      $out[] = '<pre style="white-space: pre-wrap;">';
      $out[] = $data['Entry']['subject'] . "\n";
      $out[] = $data['Entry']['text'];
      $out[] = '</pre>';
      return implode("\n", $out);
    }

    public function view($id=null) {
		Stopwatch::start('Entries->view()');

		//* redirect if no id is given
		if ( !$id ) {
			$this->Session->setFlash(__('Invalid post'));
			return $this->redirect(array( 'action' => 'index' ));
		}

		$this->Entry->id = $id;
		$this->request->data = $this->Entry->find('entry', array('conditions' => array('Entry.id' => $id)));

		//* redirect if posting doesn't exists
		if ( $this->request->data == FALSE ):
			$this->Session->setFlash(__('Invalid post'));
			return $this->redirect('/');
		endif;

		//* check if anonymous tries to access internal catgories
		if ( $this->request->data['Category']['accession'] > $this->CurrentUser->getMaxAccession() ) {
			return $this->redirect('/');
		}

    if ( !empty($this->request->params['requested']) ):
      return $this->request->data;
    endif;

		$a = array($this->request->data);
		list($this->request->data) = $a;
		$this->set('entry', $this->request->data);

		if ( $this->request->data['Entry']['user_id'] != $this->CurrentUser->getId() ):
			$this->Entry->incrementViews();
    endif;

		// @td doku
		$this->set('show_answer', (isset($this->request->data['show_answer'])) ? true : false);

    $this->_showAnsweringPanel();

		if ( $this->request->is('ajax') ):
			//* inline view
			$this->render('/Elements/entry/view_posting');
			return;
		else:
			//* full page request
			$this->set('tree', $this->Entry->treeForNode($this->request->data['Entry']['tid']));
			$this->set('title_for_layout', $this->request->data['Entry']['subject']);

		endif;

		Stopwatch::stop('Entries->view()');
	}

	public function add($id=null) {
		$this->set('form_title', __('new_entry_linktitle'));

		if ( !$this->CurrentUser->isLoggedIn() ) {
			$message = __('logged_in_users_only');

			if ( $this->request->is('ajax') ) {
				$this->set('message', $message);
				$this->render('/Elements/empty');
			} else {
				$this->Session->setFlash($message, 'flash/notice');
				return $this->redirect($this->referer());
			}
		}

		if ( !empty($this->request->data) ) {
			// insert new entry

			// prepare new entry
			$this->request->data = $this->_prepareAnswering($this->request->data);
			$this->request->data['Entry']['user_id'] = $this->CurrentUser->getId();
			$this->request->data['Entry']['name'] = $this->CurrentUser['username'];

			$new_posting = $this->Entry->createPosting($this->request->data);

			if ( $new_posting ) :
				// inserting new posting was successful

				$this->_afterNewEntry($new_posting);

				if ( $this->request->is('ajax') ):
					//* The new posting is requesting an ajax answer
					if ( $this->localReferer('action') == 'index' ) :
						//* Ajax request came from front answer on front page /entries/index
						$this->set('entry_sub', $this->Entry->read(null, $this->Entry->id));
						// ajax requests so far are always answers
						$this->set('level', '1');
						$this->render('/Elements/entry/ajax-thread_cached');
						return ;
					endif;
				else:
					// answering through POST request
					if ( $this->localReferer('action') == 'mix' ):
						// answer request came from mix ansicht
						$this->redirect(array( 'controller' => 'entries', 'action' => 'mix', $new_posting['Entry']['tid'], '#' => $this->Entry->id ));
						return;
					endif;
					// normal posting from entries/add or entries/view
					$this->redirect(array( 'controller' => 'entries', 'action' => 'view', $this->Entry->id ));
					return;
				endif;
			else :
				// Error while trying to save a post
				if ( count($this->Entry->validationErrors) === 0 ) :
					$this->Session->setFlash(__('Something clogged the tubes. Could not save entry. Try again.'), 'flash/error');
				endif;
				$headerSubnavLeftTitle = __('back_to_overview_linkname');
			endif;
		} else {
			// show answering form

			// answering is always a ajax request, prevents add/1234 GET-requests
			if(!$this->request->is('ajax') && $id !== null) {
				$this->Session->setFlash(__('js-required'), 'flash/error');
				return $this->redirect($this->referer());
			}

			$this->request->data = NULL;
			if ($id !== NULL) {
				// check if entry exists by loading its data
				$this->Entry->contain(array('User', 'Category'));
				$this->Entry->sanitize(false);
				$this->request->data = $this->Entry->findById($id);
			}

			if ( !empty($this->request->data) ):
					// new posting is answer to existing posting

					$this->_isAnsweringAllowed($this->request->data);

					// create new subentry
					$this->request->data['Entry']['pid'] = $id;
					// we assume that an answers to a nsfw posting isn't nsfw itself
					unset($this->request->data['Entry']['nsfw']);
					// subject is empty in answer-form
					unset($this->request->data['Entry']['subject']);
					$this->set('citeText', $this->request->data['Entry']['text']);

					// get notifications
					$notis = $this->Entry->Esevent->checkEventsForUser($this->CurrentUser->getId(),
							array(
									1 => array(
									'subject'	 => $this->request->data['Entry']['tid'],
									'event'		 => 'Model.Entry.replyToThread',
									'receiver' => 'EmailNotification',
							),
							)
					);
					$this->set('notis', $notis);

					// set Subnav
					$headerSubnavLeftTitle = __('back_to_posting_from_linkname',
							$this->request->data['User']['username']);
				else:
					// new posting which creates new thread
					$this->request->data['Entry']['pid'] = 0;
					$this->request->data['Entry']['tid'] = 0;

					$headerSubnavLeftTitle = __('back_to_overview_linkname');
				endif;

			if ( $this->request->is('ajax') ):
				$this->set('form_title', __('answer_marking'));
			endif;
		}

    $this->set('headerSubnavLeftTitle', $headerSubnavLeftTitle);
    $this->set('headerSubnavLeftUrl', '/entries/index');

		$this->_teardownAdd();
	}

	public function edit($id = NULL) {

		if ( !$id && empty($this->request->data) ):
			throw new NotFoundException();
		endif;

		// read old entry
		$this->Entry->id = $id;
		$this->Entry->sanitize(false);
		$old_entry = $this->Entry->find('first', array(
				'contain' => array(
						'User',
						'Category'),
				'conditions' => array('Entry.id' => $id),
		));

		// check if entry exists
		if (!$old_entry):
			throw new NotFoundException();
		endif;

		$forbidden = $this->Entry->isEditingForbidden($old_entry, $this->CurrentUser);

		switch ( $forbidden ) {
			case 'time':
				$this->Session->setFlash('Stand by your word bro\', it\'s too late. @lo',
						'flash/error');
				return $this->redirect(array( 'action' => 'view', $id ));
				break;
			case 'user':
				$this->Session->setFlash('Not your horse, Hoss! @lo', 'flash/error');
				return $this->redirect(array( 'action' => 'view', $id ));
				break;
			case true :
				$this->Session->setFlash('Something went terribly wrong. Alert the authorties now! @lo',
						'flash/error');
		}

		if (!$this->Entry->isEditingForbidden($old_entry, $this->CurrentUser)
				&& $this->Entry->isEditingForbidden($old_entry, $this->CurrentUser->mockUserType('user'))) {
			$this->Session->setFlash(__('notice_you_are_editing_as_mod'), 'flash/warning');
		}

		if ( !empty($this->request->data) ) {
			$this->request->data = $this->_prepareAnswering($this->request->data);
			// try to save entry
			$this->request->data['Entry']['edited'] = date("Y-m-d H:i:s");
			$this->request->data['Entry']['edited_by'] = $this->CurrentUser['username'];

			if ( $new_entry = $this->Entry->save($this->request->data) ) {
				// new entry was saved
				$this->_afterNewEntry(am($this->request['data'], $old_entry));
				return $this->redirect(array( 'action' => 'view', $id ));
			} else {
				$this->Session->setFlash(__('Something clogged the tubes. Could not save entry. Try again.'));
			}
		}

		$this->request->data = am($old_entry, $this->request->data);

		// get text of parent entry for citation
		$parent_entry_id = $old_entry['Entry']['pid'];
		if ($parent_entry_id > 0) {
			$parent_entry = $this->_getRawParentEntry($parent_entry_id);
			$this->set('citeText', $parent_entry['Entry']['text']);
		}

		// get notifications
			$notis = $this->Entry->Esevent->checkEventsForUser($old_entry['Entry']['user_id'],
					array(
							array(
									'subject'	 => $old_entry['Entry']['id'],
									'event'		 => 'Model.Entry.replyToEntry',
									'receiver' => 'EmailNotification',
							),
							array(
									'subject'	 => $old_entry['Entry']['tid'],
									'event'		 => 'Model.Entry.replyToThread',
									'receiver' => 'EmailNotification',
							),
					)
			);
			$this->set('notis', $notis);

		// set headers
    $this->set('headerSubnavLeftUrl', '/entries/index');
    $this->set(
        'headerSubnavLeftTitle',
		    __('back_to_posting_from_linkname', $this->request->data['User']['username'])
        );
		$this->set('headerSubnavLeftUrl', array( 'action' => 'view', $id ));

		$this->set('form_title', __('edit_linkname'));

		$this->_teardownAdd();

		$this->render('/Entries/add');
	}

	public function delete($id = NULL) {
		// $id must be set
		if ( !$id ) {
			$this->redirect('/');
		}

		// Confirm user is allowed
		if ( !$this->CurrentUser->isMod() ) {
			$this->redirect('/');
		}

		// Delete Entry
		$this->Entry->id = $id;
		$success = $this->Entry->threadDelete();

		// Redirect
		if ( $success ) {
			$this->_emptyCache($id, $id);
		} else {
			$this->Session->setFlash(__('delete_tree_error'), 'flash/error');
			$this->redirect($this->referer());
		}
		$this->redirect('/');
	}

//end delete()

	/**
	 * Empty function for benchmarking
	 */
	public function e()  {
		Stopwatch::start('Entries->e()');
		Stopwatch::stop('Entries->e()');
	}

	public function search() {

//		debug($this->request->data);
//		debug($this->request->params);
//		debug($this->passedArgs);
		// determine start year for dropdown in form
		$found_entry = $this->Entry->find('first',
						array( 'order' => 'Entry.id ASC', 'contain' => false ));
		if ( $found_entry !== FALSE ) {
			$start_date = strtotime($found_entry['Entry']['time']);
		} else {
			$start_date = time();
		}
		$this->set('start_year', date('Y', $start_date));

		// get categories for dropdown
		$categories = $this->Entry->Category->getCategoriesSelectForAccession(
				$this->CurrentUser->getMaxAccession());
		$this->set('categories', $categories);

		//* calculate current month and year
		if ( empty($this->request->data['Entry']['month']) && empty($searchStartMonth))  {
			// start in last month
			//	$start_date = mktime(0,0,0,((int)date('m')-1), 28, (int)date('Y'));
			$searchStartMonth = date('n', $start_date);
			$searchStartYear  = date('Y', $start_date);
		}

		// extract search_term for simple search
		$searchTerm = '';
		if ( isset($this->request->data['Entry']['search_term']) ) {
			$searchTerm = $this->request->data['Entry']['search_term'];
		} elseif ( isset($this->request->params['named']['search_term']) ) {
			$searchTerm = $this->request->params['named']['search_term'];
		} elseif ( isset($this->request['url']['search_term']) ) {
			// search_term is send via get parameter
			$searchTerm = $this->request['url']['search_term'];
		}
		$this->set('search_term', $searchTerm);

		if ( isset($this->passedArgs['adv']) ) {
			$this->request->params['data']['Entry']['adv'] = 1;
		}

		if ( !isset($this->request->data['Entry']['adv']) && !isset($this->request->params['named']['adv']) ) {
			// Simple Search
			if ( $searchTerm ) {
				Router::connectNamed(array( 'search_term' ));

				$this->passedArgs['search_term'] = $searchTerm;
				/* stupid apache rewrite urlencode bullshit */
				// $this->passedArgs['search_term'] = urlencode(urlencode($search_term));

				if ( $searchTerm ) {
          $searchTerm = Sanitize::escape($searchTerm);
					$internal_search_term = preg_replace('/(^|\s)(?!-)/i', ' +', $searchTerm);
					$this->paginate = array(
							'fields' => "*, (MATCH (Entry.subject) AGAINST ('$internal_search_term' IN BOOLEAN MODE)*2) + (MATCH (Entry.text) AGAINST ('$internal_search_term' IN BOOLEAN MODE)) + (MATCH (Entry.name) AGAINST ('$internal_search_term' IN BOOLEAN MODE)*4) AS rating",
							'conditions' => array(
                "MATCH (Entry.subject, Entry.text, Entry.name) AGAINST ('$internal_search_term' IN BOOLEAN MODE)",
                'Entry.category' => $this->Entry->Category->getCategoriesForAccession($this->CurrentUser->getMaxAccession())),
							'order' => 'rating DESC, `Entry`.`time` DESC',
							'limit' => 25,
					);
					$found_entries = $this->paginate('Entry');

					$this->set('FoundEntries', $found_entries);
					$this->request->data['Entry']['search']['term'] = $searchTerm;
				}
			}
		} else {
			// Advanced Search
			if (isset($this->request->params['named']['month'])):
				$searchStartMonth = (int)$this->request->params['named']['month'];
				$searchStartYear  = (int)$this->request->params['named']['year'];
			endif;

			$this->Prg->commonProcess();
			$paginateSettings = array();
			$paginateSettings['conditions'] = $this->Entry->parseCriteria(
					$this->request->params['named']);
			$paginateSettings['conditions']['time >'] = date(
					'Y-m-d H:i:s', mktime( 0, 0, 0, $searchStartMonth, 1, $searchStartYear ));

			if((int)$this->request->data['Entry']['category'] !== 0) {
				if (!isset($categories[(int)$this->request->data['Entry']['category']])) {
					throw new NotFoundException;
				}
			} else {
				$paginateSettings['conditions']['Entry.category'] = 
					$this->Entry->Category->getCategoriesForAccession(
							$this->CurrentUser->getMaxAccession());
			}

			$paginateSettings['order'] = array('Entry.time' => 'DESC');
			$paginateSettings['limit'] = 25;
			$this->paginate = $paginateSettings;
			$this->set('FoundEntries', $this->paginate());
		}

		if(!isset($this->request->data['Entry']['category'])) {
			$this->request->data['Entry']['category']	= 0;
		}
		$this->request->data['Entry']['month'] = $searchStartMonth;
		$this->request->data['Entry']['year']  = $searchStartYear;
	}

	public function preview() {
		if ( !$this->request->is('ajax') ) {
			$this->redirect('/');
		}

		$this->request->data = $this->_prepareAnswering($this->request->data);

		extract($this->request->data['Entry']);
		unset($this->request->data);

		$this->request->data = array( );

		$this->request->data['Entry']['pid'] = $pid;
		$this->request->data['Entry']['subject'] = $subject;
		$this->request->data['Entry']['text'] = $text;
		$this->request->data['Entry']['category'] = $category;
		$this->request->data['Entry']['nsfw'] = $nsfw;
		$this->request->data['Entry']['fixed'] = false;
		$this->request->data['Entry']['ip'] = '';


		$this->Entry->set($this->request->data);
		$validate = $this->Entry->validates(array( 'fieldList' => array( 'subject', 'text', 'category' ) ));
		$errors = $this->Entry->validationErrors;

		if ( count($errors) === 0 ) :
		//* no validation errors
			// Sanitize before validation: maxLength will fail because of html entities
			$this->request->data['Entry']['subject'] = Sanitize::html($subject);
			$this->request->data['Entry']['text'] = Sanitize::html($text);
			$this->request->data['Entry']['views'] = 0;
			$this->request->data['Entry']['time'] = date("Y-m-d H:i:s");


			$this->request->data['User'] = $this->CurrentUser->getSettings();

			$this->request->data = array_merge($this->request->data,
					$this->Entry->Category->find(
							'first',
							array(
							'conditions' => array(
									'id' => $this->request->data['Entry']['category']
							),
							'contain' => false,
							)
					));
			$this->set('entry', $this->request->data);
		else :
		//* validation errors
			foreach ( $errors as $field => $error ) {
				$message[] = __d('nondynamic', $field) . ": " . __d('nondynamic', $error[0]);
			}
			$this->set('message', $message);
			$this->render('/Elements/flash/error');
		endif;
	}

	public function merge($id = null) {
		if (!$id) { throw new NotFoundException(); }

		if (!$this->CurrentUser->isMod() && !$this->CurrentUser->isAdmin()) {
			throw new MethodNotAllowedException;
		}

		$this->Entry->contain();
		$data = $this->Entry->findById($id);

		if (!$data || (int)$data['Entry']['pid'] !== 0) {
			throw new NotFoundException();
		}

		// perform move operation
		if (isset($this->request->data['Entry']['targetId'])) {
			$targetId = $this->request->data['Entry']['targetId'];
			$this->Entry->id = $id;
			if ($this->Entry->threadMerge($targetId)) {
				// success
				$this->Entry->contain();
				$targetEntry = $this->Entry->findById($targetId);
				$this->_emptyCache($targetEntry['Entry']['id'], $targetEntry['Entry']['id']);
				return $this->redirect('/entries/view/' . $id);
			} else {
				$this->Session->setFlash(__("Error"), 'flash/error');
			}
		}

		$this->layout = 'admin';
		$this->request->data = $data;
	}

	public function ajax_toggle($id = null, $toggle = null) {
		$this->autoLayout = false;
		$this->autoRender = false;

		if ( !$id || !$toggle || !$this->request->is('ajax') )
			return;

		// check if the requested toggle is allowed to be changed via this function
		$allowed_toggles = array(
				'fixed',
				'locked',
		);
		if ( !in_array($toggle, $allowed_toggles) ) {
			$this->request->data = false;

			// check is user is allowed to perform operation
			// luckily we only mod options in the allowed toggles
		} elseif ( $this->CurrentUser->isMod() === false ) {
			$this->request->data = false;
		}
		// let's toggle
		else {
			$this->Entry->id = $id;
			$this->request->data = $this->Entry->toggle($toggle);
			$tid = $this->Entry->field('tid');
			$this->_emptyCache($id, $tid);
			return ($this->request->data == 0) ? __d('nondynamic', $toggle . '_set_entry_link') : __d('nondynamic', $toggle . '_unset_entry_link');
		}

		$this->set('json_data', (string) $this->request->data);
		$this->render('/Elements/json/json_data');

		// perform toggle
	}

//end ajax_toggle()

		public function beforeFilter() {
			parent::beforeFilter();
			Stopwatch::start('Entries->beforeFilter()');

			$this->Auth->allow('feed', 'index', 'view', 'mix');

			if ($this->request->action === 'index') {
				if ($this->CurrentUser->getId() && $this->CurrentUser['user_forum_refresh_time'] > 0) {
					$this->set('autoPageReload',
							$this->CurrentUser['user_forum_refresh_time'] * 60);
				}
				$this->_setAppStats();
			}
			if ($this->request->action !== 'index') {
				$this->_loadSmilies();
			}

			$this->_automaticalyMarkAsRead();

			Stopwatch::stop('Entries->beforeFilter()');
		}

		protected function _automaticalyMarkAsRead() {
			// ignore browser prefetch
			if ( (env('HTTP_X_PURPOSE') === 'preview') // Safari
				|| (env('HTTP_X_MOZ') === 'prefetch') // Firefox
				) {
				return;
			} 

			if ($this->CurrentUser->isLoggedIn() && $this->CurrentUser['user_automaticaly_mark_as_read']):
				if (
						($this->request->params['action'] === 'index' && $this->Session->read('paginator.lastPage') == 1) // deprecated
				// OR (isset($this->request->params['named']['markAsRead']) || isset($this->request->params['named']['setAsRead'])) // current
				):
					// initiate sessions last_refresh_tmp for new sessions
					if (!$this->Session->read('User.last_refresh_tmp')) {
						$this->Session->write('User.last_refresh_tmp', time());
					}
					if (
							($this->localReferer('controller') === 'entries' && $this->localReferer('action') === 'index') // deprecated
					// OR (isset($this->request->params['named']['setAsRead'])) // current
					):
						// a second session A don't accidentaly mark something as read that isn't read on session B
						if ($this->Session->read('User.last_refresh_tmp')
								&& $this->Session->read('User.last_refresh_tmp') > strtotime($this->CurrentUser['last_refresh'])
						) {
							if ($this->Session->read('User_last_refresh_disabled')) {
								$this->Session->write('User_last_refresh_disabled', false);
							} else {
								$this->CurrentUser->LastRefresh->set();
							}
						}
						$this->Session->write('User.last_refresh_tmp', time());
					else:
						$this->CurrentUser->LastRefresh->setMarker();
					endif;
				endif;
			endif;
		}

	protected function _emptyCache($id, $tid) {
    $this->CacheTree->delete($tid);
		clearCache("element_{$id}_entry_thread_line_cached", 'views', '');
		clearCache("element_{$id}_entry_view_content", 'views', '');
		Cache::clearGroup('postings', 'postings');
	}

	protected function _afterNewEntry($newEntry) {
			$this->_emptyCache($newEntry['Entry']['id'], $newEntry['Entry']['tid']);
			// set notifications
			if (isset($newEntry['Event'])) {
				$notis = array(
						array(
								'subject' 			=> $newEntry['Entry']['id'],
								'event' 				=> 'Model.Entry.replyToEntry',
								'receiver'			=> 'EmailNotification',
								'set' 					=> $newEntry['Event'][1]['event_type_id'],
						),
						array(
								'subject' 			=> $newEntry['Entry']['tid'],
								'event' 				=> 'Model.Entry.replyToThread',
								'receiver'			=> 'EmailNotification',
								'set' 					=> $newEntry['Event'][2]['event_type_id'],
						),
				);
				$this->Entry->Esevent->notifyUserOnEvents($newEntry['Entry']['user_id'], $notis);
			}
	}

	protected function _isAnsweringAllowed($parent_entry) {
		$forbidden = $this->Entry->isAnsweringForbidden($parent_entry);
		if ($forbidden) {
			throw new ForbiddenException;
		}
	}

	/**
	 * Gets the thread ids of all threads which should be visisble on the an
	 * entries/index/# page.
	 *
	 * @param CurrentUserComponent $User
	 * @return array
	 */
	protected function _getInitialThreads(CurrentUserComponent $User) {
		Stopwatch::start('Entries->_getInitialThreads() Paginate');
		$sort_order = 'Entry.' . ($User['user_sort_last_answer'] == FALSE ? 'time' : 'last_answer');
		$order = array( 'Entry.fixed' => 'DESC', $sort_order => 'DESC' );

			// default for logged-in and logged-out users
			$cats				 = $this->Entry->Category->getCategoriesForAccession($User->getMaxAccession());

			// get data for category chooser
			$categoryChooser = $this->Entry->Category->getCategoriesSelectForAccession(
					$User->getMaxAccession());
			$this->set('categoryChooser', $categoryChooser);

			$catCT			 = __('All Categories');
			$catC_isUsed = false;

			// category chooser
			if ($User->isLoggedIn()) {
				if (Configure::read('Saito.Settings.category_chooser_global')
						|| (Configure::read('Saito.Settings.category_chooser_user_override') && $User['user_category_override'])
				) {
					$catC_isUsed = true;
					/* merge the user-cats with all-cats to include categories which are
						* new since the user updated his custum-cats the last time
						* array (4 => '4', 7 => '7', 13 => '13') + array (4 => true, 7 => '0')
						* becomes
						* array (4 => true, 7 => '0', 13 => '13')
						* with 13 => '13' trueish */
					$user_cats = $User['user_category_custom'] + $cats;
					/* then filter for zeros to get only the user categories
						* array (4 => true, 13 => '13') */
					$user_cats = array_filter($user_cats);
					$user_cats = array_intersect_key($user_cats, $cats);
					$this->set('categoryChooserChecked', $user_cats);

					if (!$User->isLoggedIn()) {
						// non logged in user sees his accessions i.e. the default set
					} elseif ((int)$User['user_category_active'] === -1) {
						// user has choosen to see all available categories i.e. the default set
					} elseif ((int)$User['user_category_active'] > 0) {
						// logged in users sees his active group if he has access rights
						$cats = array_intersect_key($cats,
								array($User['user_category_active']	 => 1));
						$catCT												 = $User['user_category_active'];
					} elseif (empty($User['user_category_custom'])) {
						// for whatever reason we should see a custom category, but there are no set yet
					} elseif (!empty($User['user_category_custom'])) {
						// but if he has no active group and a custom groups set he sees his custom group
						$cats	 = array_keys($user_cats);
						$catCT = __('Custom');
					}

					$this->set('categoryChooserTitleId', $catCT);
				}
			}
			$this->set('categoryChooserIsUsed', $catC_isUsed);

			$this->paginate = array(
				/* Whenever you change the conditions here check if you have to adjust
				 * the db index. Running this query without appropriate db index is a huge
				 * performance bottleneck!
				 */
				'conditions' => array(
						'pid' => 0,
						'Entry.category' => $cats,
				),
				'contain' => false,
				'fields' => 'id, pid, tid, time, last_answer',
				'limit' => Configure::read('Saito.Settings.topics_per_page'),
				'order' => $order,
        'getInitialThreads' => 1,
				)
		;
		$initial_threads = $this->paginate();

		$initial_threads_new = array( );
		foreach ( $initial_threads as $k => $v ) {
			$initial_threads_new[$k] = $v["Entry"];
		}
		Stopwatch::stop('Entries->_getInitialThreads() Paginate');
		return array( 'initialThreads' => $initial_threads_new, 'order' => $order );
	}

	protected function _teardownAdd() {
		//* find categories for dropdown
		$categories = $this->Entry->Category->getCategoriesSelectForAccession($this->CurrentUser->getMaxAccession());
		$this->set('categories', $categories);
	}

  /**
   * Decide if an answering panel is show when rendering a posting
   */
  protected function _showAnsweringPanel() {
    $showAnsweringPanel = FALSE;

		if ($this->CurrentUser->isLoggedIn()) {
			// Only logged in users see the answering buttons if they …
			if ( // … directly on entries/view but not inline
					($this->request->action === 'view' && !$this->request->is('ajax'))
					// … directly in entries/mix
					|| $this->request->action === 'mix'
					// … inline viewing … on entries/index.
					|| ( $this->localReferer('controller') === 'entries' && $this->localReferer('action') === 'index')
			):
				$showAnsweringPanel = TRUE;
			endif;
		}

    $this->set('showAnsweringPanel', $showAnsweringPanel);

  }

	protected function _prepareAnswering($data) {
			$pid = (int)$data['Entry']['pid'];
			if ( $pid > 0 ) {
				$parent_entry = $this->_getRawParentEntry($pid);
				$this->_isAnsweringAllowed($parent_entry);
				$this->_swapEmptySubject($data, $parent_entry);
			}
			return $data;
	}

	protected function _getRawParentEntry($id) {
		$this->Entry->contain();
		$this->Entry->sanitize(false);
		$parent_entry = $this->Entry->findById($id);
		if(!$parent_entry) {
			throw new NotFoundException;
		}
		return $parent_entry;
	}

	protected function _swapEmptySubject(&$entry, $parent) {
			// if send entry is empty we assume that it's a 'Re:' and use the parent subject
			if (empty($entry['Entry']['subject'])) {
				$entry['Entry']['subject'] = $parent['Entry']['subject'];
			}
		}

}

?>