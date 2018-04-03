<?php

	use Saito\User\Categories;

	App::uses('AppController', 'Controller');

	class EntriesController extends AppController {

		public $name = 'Entries';

		public $helpers = [
			'EntryH',
			'MarkitupEditor',
			'Shouts',
			'Text',
		];

		public $components = [
			'Shouts'
		];

		public function index() {
			Stopwatch::start('Entries->index()');

			$this->_prepareSlidetabData();

			//= determine user sort order
			$sortKey = 'Entry.';
			if (!$this->CurrentUser->isLoggedIn() || $this->CurrentUser['user_sort_last_answer']) {
				$sortKey .= 'last_answer';
			} else {
				$sortKey .= 'time';
			}
			$order = ['Entry.fixed' => 'DESC', $sortKey => 'DESC'];

			//= get threads
			$initials = $this->_getInitialThreads($this->CurrentUser, $order);
			$threads = $this->Entry->treesForThreads($initials, $order);
			foreach ($threads as $tid => $thread) {
				$threads[$tid] = $this->dic->newInstance('\Saito\Posting\Posting', ['rawData' => $thread]);
			}
			$this->set('entries', $threads);

			$currentPage = 1;
			if (isset($this->request->named['page']) && $this->request->named['page'] != 1) {
				$currentPage = (int)$this->request->named['page'];
				$this->set('title_for_layout', __('page') . ' ' . $currentPage);
			}
			if ($currentPage === 1 && $this->CurrentUser->isLoggedIn()
				&& $this->CurrentUser['user_automaticaly_mark_as_read']
			) {
				$this->set('markAsRead', true);
			}
			// @bogus
			$this->Session->write('paginator.lastPage', $currentPage);
			$this->showDisclaimer = true;
			$this->set('allowThreadCollapse', true);

			Stopwatch::stop('Entries->index()');
		}

		public function feed() {
			Configure::write('debug', 0);

			if (isset($this->request->params['named']['depth']) &&
					$this->request->params['named']['depth'] === 'start'
			) {
				$title = __('Last started threads');
				$order = 'time DESC';
				$conditions['pid'] = 0;
			} else {
				$title = __('Last entries');
				$order = 'last_answer DESC';
			}

			$conditions['category_id'] = $this->CurrentUser->Categories->getAllowed();

			$entries = $this->Entry->find(
				'feed',
				[
					'conditions' => $conditions,
					'order' => $order
				]
			);
			$this->set('entries', $entries);

			// serialize for JSON
			$this->set('_serialize', 'entries');
			$this->set('title', $title);
		}

		/**
		 * Mix view
		 *
		 * @param $tid
		 * @throws NotFoundException
		 */
		public function mix($tid) {
			if (!$tid) {
				$this->redirect('/');
			}
			$entries = $this->Entry->treeForNode($tid,
					['root' => true, 'complete' => true]);

			if (empty($entries)) {
				throw new NotFoundException();
			}

			// check if anonymous tries to access internal categories
			$root = reset($entries);
			$accession = $root['Category']['accession'];
			if (!$this->CurrentUser->Categories->isAccessionAuthorized($accession)) {
				$this->_requireAuth();
				return;
			}

			$this->_setRootEntry($root);
			$this->_setTitleFromEntry($root, __('view.type.mix'));

			$entries = $this->dic->newInstance('\Saito\Posting\Posting', ['rawData' => $root]);
			$this->set('entries', $entries);

			$this->_showAnsweringPanel();

			$this->_incrementViews($root, 'thread');

			$this->_marMixThread = $tid;
		}

/**
 * load front page force all entries mark-as-read
 */
		public function update() {
			$this->autoRender = false;
			$this->CurrentUser->LastRefresh->set('now');
			$this->redirect('/entries/index');
		}

/**
 * Outputs raw markup of an posting $id
 *
 * @param int $id
 *
 * @return string
 */
		public function source($id = null) {
			$this->autoRender = false;

			$data = $this->requestAction('/entries/view/' . $id);

			$out = array();
			$out[] = '<pre style="white-space: pre-wrap;">';
			$out[] = $data['Entry']['subject'] . "\n";
			$out[] = $data['Entry']['text'];
			$out[] = '</pre>';
			return implode("\n", $out);
		}

		public function view($id = null) {
			Stopwatch::start('Entries->view()');

			// redirect if no id is given
			if (!$id) {
				$this->Session->setFlash(__('Invalid post'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Entry->id = $id;
			$entry = $this->Entry->get($id);

			// redirect if posting doesn't exists
			if ($entry == false) {
				$this->Session->setFlash(__('Invalid post'));
				$this->redirect('/');
				return;
			}

			// check if anonymous tries to access internal categories
			$accession = $entry['Category']['accession'];
			if (!$this->CurrentUser->Categories->isAccessionAuthorized($accession)) {
				$this->_requireAuth();
				return;
			}

			$this->_incrementViews($entry);

			// for /source/<id> view
			if (!empty($this->request->params['requested'])) {
				return $entry;
			}

			$this->set('entry', $entry);

			$this->_setRootEntry($entry);
			$this->_showAnsweringPanel();

			$this->CurrentUser->ReadEntries->set($entry);

			// inline open
			if ($this->request->is('ajax')) {
				$this->render('/Elements/entry/view_posting');
				return;
			}

			// full page request
			$this->set('tree',
					$this->Entry->treeForNode($entry['Entry']['tid'], ['root' => true]));
			$this->_setTitleFromEntry($entry);

			Stopwatch::stop('Entries->view()');
		}

/**
 * @param null $id
 *
 * @return string
 * @throws ForbiddenException
 */
		public function add($id = null) {
			$this->set('title_for_layout', __('Write a New Entry'));

			//# insert new entry
			if (!empty($this->request->data)) {
				$newPosting = $this->Entry->createPosting($this->request->data);

				// inserting new posting was successful
				if ($newPosting !== false) :
					$this->_setNotifications($newPosting + $this->request->data);
					if ($this->request->is('ajax')) :
						// Ajax request came from front answer on front page /entries/index
						if ($this->localReferer('action') === 'index') {
							$this->autoRender = false;

							return json_encode(
								[
									'id' => (int)$newPosting['Entry']['id'],
									'pid' => (int)$newPosting['Entry']['pid'],
									'tid' => (int)$newPosting['Entry']['tid']
								]
							);
						} else {
							$this->_stop();
						}
					// answering through POST request
					else :
						if ($this->localReferer('action') === 'mix') {
							// answer request came from mix ansicht
							$this->redirect(
								[
									'controller' => 'entries',
									'action' => 'mix',
									$newPosting['Entry']['tid'],
									'#' => $this->Entry->id
								]
							);

						} else {
							// normal posting from entries/add or entries/view
							$this->redirect(
								[
									'controller' => 'entries',
									'action' => 'view',
									$this->Entry->id
								]
							);

						}
						return;
					endif;
				else :
					// Error while trying to save a post
					if (count($this->Entry->validationErrors) === 0) {
						$this->Session->setFlash(
							__(
								'Something clogged the tubes. Could not save entry. Try again.'
							),
							'flash/error'
						);
					}
				endif;

			// show add form
			} else {
				$isAnswer = $id !== null;
				$this->request->data = null;

				if ($isAnswer) {
					if ($this->request->is('ajax') === false) {
						$this->redirect($this->referer());
						return;
					}

					$this->request->data = $this->Entry->get($id);
					$posting = $this->dic->newInstance('\Saito\Posting\Posting',
						['rawData' => $this->request->data]);

					if ($posting->isAnsweringForbidden()) {
						throw new ForbiddenException;
					}

					// create new subentry
					unset($this->request->data['Entry']['id']);
					$this->request->data['Entry']['pid'] = $id;
					$this->set('citeSubject', $this->request->data['Entry']['subject']);
					// subject is empty in answer-form
					unset($this->request->data['Entry']['subject']);
					$this->set('citeText', $this->request->data['Entry']['text']);
					// text field is empty in answer
					unset($this->request->data['Entry']['text']);

					// get notifications
					$notis = $this->Entry->Esevent->checkEventsForUser(
						$this->CurrentUser->getId(),
						array(
							1 => array(
								'subject' => $this->request->data['Entry']['tid'],
								'event' => 'Model.Entry.replyToThread',
								'receiver' => 'EmailNotification',
							),
						)
					);
					$this->set('notis', $notis);

					// set Subnav
					$headerSubnavLeftTitle = __(
						'back_to_posting_from_linkname',
						$this->request->data['User']['username']
					);
					$this->set('headerSubnavLeftTitle', $headerSubnavLeftTitle);

					$this->set('title_for_layout', __('Write a Reply'));
				} else {
					// new posting which creates new thread
					$this->request->data['Entry']['pid'] = 0;
					$this->request->data['Entry']['tid'] = 0;
				}
			}

			$this->set('is_answer', (int)$this->request->data['Entry']['pid'] !== 0);
			$this->set('is_inline', (int)$this->request->data['Entry']['pid'] !== 0);
			$this->set('form_id', $this->request->data['Entry']['pid']);
			$this->_setAddViewVars();
		}

		public function threadLine($id) {
			$entry = $this->Entry->get($id);
			$accession = $entry['Category']['accession'];
			if (!$this->CurrentUser->Categories->isAccessionAuthorized($accession)) {
				$this->_requireAuth();
				return;
			}
			$this->set('entry_sub', $entry);
			// ajax requests so far are always answers
			$this->set('level', '1');
		}

/**
 * @param null $id
 *
 * @throws NotFoundException
 * @throws BadRequestException
 */
		public function edit($id = null) {
			if (empty($id)) {
				throw new BadRequestException();
			}

			$oldEntry = $this->Entry->get($id);
			if (!$oldEntry) {
				throw new NotFoundException();
			}

			/** * @var \Saito\Posting\Posting */
			$posting = $this->dic->newInstance('\Saito\Posting\Posting', ['rawData' => $oldEntry]);

			switch ($posting->isEditingAsCurrentUserForbidden()) {
				case 'time':
					$this->Session->setFlash(
						'Stand by your word bro\', it\'s too late. @lo',
						'flash/error'
					);
					$this->redirect(['action' => 'view', $id]);
					return;
				case 'user':
					$this->Session->setFlash('Not your horse, Hoss! @lo', 'flash/error');
					$this->redirect(['action' => 'view', $id]);
					return;
				case true :
					$this->Session->setFlash(
						'Something went terribly wrong. Alert the authorities now! @lo',
						'flash/error'
					);
					return;
			}

			// try to save edit
			if (!empty($this->request->data)) {
				$data = $this->request->data;
				$data['Entry']['id'] = $id;
				$newEntry = $this->Entry->update($data);
				if ($newEntry) {
					$this->_setNotifications(am($this->request['data'], $oldEntry));
					$this->redirect(['action' => 'view', $id]);
					return;
				} else {
					$this->Session->setFlash(__('Something clogged the tubes. Could not save entry. Try again.'));
				}
			}

			// show editing form
			if ($posting->isEditingWithRoleUserForbidden()) {
				$this->Session->setFlash(__('notice_you_are_editing_as_mod'), 'flash/warning');
			}

			$this->request->data = Hash::merge($oldEntry, $this->request->data);

			// get text of parent entry for citation
			$parentEntryId = $oldEntry['Entry']['pid'];
			if ($parentEntryId > 0) {
				$parentEntry = $this->Entry->get($parentEntryId);
				$this->set('citeText', $parentEntry['Entry']['text']);
			}

			// get notifications
			$notis = $this->Entry->Esevent->checkEventsForUser(
				$oldEntry['Entry']['user_id'],
				array(
					array(
						'subject' => $oldEntry['Entry']['id'],
						'event' => 'Model.Entry.replyToEntry',
						'receiver' => 'EmailNotification',
					),
					array(
						'subject' => $oldEntry['Entry']['tid'],
						'event' => 'Model.Entry.replyToThread',
						'receiver' => 'EmailNotification',
					),
				)
			);
			$this->set('notis', $notis);

			$this->set('is_answer', (int)$this->request->data['Entry']['pid'] !== 0);
			$this->set('is_inline', false);
			$this->set('form_id', $this->request->data['Entry']['pid']);

			// set headers
			$this->set(
				'headerSubnavLeftTitle',
				__('back_to_posting_from_linkname', $this->request->data['User']['username'])
			);
			$this->set('headerSubnavLeftUrl', ['action' => 'view', $id]);
			$this->set('form_title', __('edit_linkname'));
			$this->_setAddViewVars();
			$this->render('/Entries/add');
		}

/**
 * @param null $id
 *
 * @throws NotFoundException
 * @throws MethodNotAllowedException
 */
	public function delete($id = null) {
		if (!$id) {
			throw new NotFoundException;
		}

		if (!$this->CurrentUser->isMod()) {
			throw new MethodNotAllowedException;
		}

		$this->Entry->id = $id;
		$this->Entry->contain();
		$entry = $this->Entry->findById($id);

		if (!$entry) {
			throw new NotFoundException;
		}

		// Delete Entry
		$success = $this->Entry->deleteNode($id);

		// Redirect
		if ($success) {
			if ($this->Entry->isRoot($entry)) {
				$this->Session->setFlash(__('delete_tree_success'), 'flash/success');
				$this->redirect('/');
			} else {
				$this->Session->setFlash(__('delete_subtree_success'), 'flash/success');
				$this->redirect('/entries/view/' . $entry['Entry']['pid']);
			}
		} else {
			$this->Session->setFlash(__('delete_tree_error'), 'flash/error');
			$this->redirect($this->referer());
		}
		$this->redirect('/');
	}

/**
 * Empty function for benchmarking
 */
		public function e() {
			Stopwatch::start('Entries->e()');
			Stopwatch::stop('Entries->e()');
		}

		/**
		 * Marks sub-entry $id as solution to its current root-entry
		 *
		 * @param $id
		 * @throws BadRequestException
		 * @throws ForbiddenException
		 * @throws Exception
		 */
		public function solve($id) {
			if (!$this->CurrentUser->isLoggedIn()) {
				throw new ForbiddenException;
			}
			$this->autoRender = false;
			try {
				$success = $this->Entry->toggleSolve($id);
				if (!$success) {
					throw new Exception;
				}
			} catch (Exception $e) {
				throw new BadRequestException;
			}
		}

/**
 * @return string
 * @throws MethodNotAllowedException
 * @throws BadRequestException
 * @throws ForbiddenException
 */
		public function preview() {
			if ($this->CurrentUser->isLoggedIn() === false) {
				throw new ForbiddenException();
			}
			if ($this->request->is('ajax') === false) {
				throw new BadRequestException();
			}
			if ($this->request->is('get')) {
				throw new MethodNotAllowedException();
			}

			$data = $this->request->data;
			$data = $data['Entry'];
			$newEntry = array(
				'Entry' => array(
					'id' => 'preview',
					'pid' => $data['pid'],
					'subject' => $data['subject'],
					'text' => $data['text'],
					'category_id' => $data['category_id'],
					'edited_by' => null,
					'fixed' => false,
					'solves' => 0,
					'views' => 0,
					'ip' => '',
					'time' => date("Y-m-d H:i:s")
				)
			);
			$this->Entry->prepare($newEntry);
			$this->Entry->set($newEntry);

			$this->Entry->validates(['fieldList' => ['subject', 'text', 'category_id']]);
			$errors = $this->Entry->validationErrors;

			if (count($errors) === 0) :
				// no validation errors
				$newEntry['User'] = $this->CurrentUser->getSettings();
				$newEntry = array_merge(
					$newEntry,
					$this->Entry->Category->find(
						'first',
						array(
							'conditions' => array(
								'id' => $newEntry['Entry']['category_id']
							),
							'contain' => false,
						)
					)
				);
				$entry = $this->dic->newInstance('\Saito\Posting\Posting', ['rawData' => $newEntry]);
				$this->set('entry', $entry);
			else :
				// validation errors
				foreach ($errors as $field => $error) {
					$message = __d('nondynamic', $field) . ": " . __d( 'nondynamic', $error[0]);
					$this->JsData->addAppJsMessage(
						$message,
						[
							'type' => 'error',
							'channel' => 'form',
							'element' => '#Entry' . array_reduce(
									explode('_', $field),
									function ($carry, $item) {
										return $carry . ucfirst($item);
									},
									''
								)
						]
					);
				}
				$this->autoRender = false;
				return json_encode($this->JsData->getAppJsMessages());
			endif;
		}

/**
 * @param null $id
 *
 * @throws NotFoundException
 * @throws MethodNotAllowedException
 */
		public function merge($id = null) {
			if (!$id) {
				throw new NotFoundException();
			}

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
					$this->redirect('/entries/view/' . $id);
					return;
				} else {
					$this->Session->setFlash(__("Error"), 'flash/error');
				}
			}

			$this->layout = 'admin';
			$this->request->data = $data;
		}

/**
 * @param null $id
 * @param null $toggle
 *
 * @return translated
 */
		public function ajax_toggle($id = null, $toggle = null) {
			$this->autoLayout = false;
			$this->autoRender = false;

			if (!$id || !$toggle || !$this->request->is('ajax')) {
				return;
			}

			// check if the requested toggle is allowed to be changed via this function
			$allowedToggles = array(
					'fixed',
					'locked',
			);
			if (!in_array($toggle, $allowedToggles)) {
				$this->request->data = false;

				// check is user is allowed to perform operation
				// luckily we only mod options in the allowed toggles
			} elseif ($this->CurrentUser->isMod() === false) {
				$this->request->data = false;
			} else {
				//* let's toggle
				$this->Entry->id = $id;
				$this->request->data = $this->Entry->toggle($toggle);
				return ($this->request->data == 0) ? __d('nondynamic', $toggle . '_set_entry_link') : __d('nondynamic', $toggle . '_unset_entry_link');
			}

			$this->set('json_data', (string)$this->request->data);
			$this->render('/Elements/json/json_data');
		}

		public function beforeFilter() {
			parent::beforeFilter();
			Stopwatch::start('Entries->beforeFilter()');

			$this->_automaticalyMarkAsRead();

			$this->Security->unlockedActions = ['preview', 'solve', 'view'];
			$this->Auth->allow('feed', 'index', 'view', 'mix', 'update');

			switch ($this->request->action) {
				case 'index':
					$this->_setAutoRefreshTime();
					break;
				case 'add':
				case 'edit':
					if ($this->Components->enabled('Security')) {
						$this->Components->disable('Security');
					}
			}

			Stopwatch::stop('Entries->beforeFilter()');
		}

		public function afterFilter() {
			if (isset($this->_marMixThread)) {
				$entries = $this->Entry->find('all',
						[
								'contain' => false,
								'conditions' => ['Entry.tid' => $this->_marMixThread],
								'fields' => ['Entry.id', 'Entry.time']
						]);
				$this->CurrentUser->ReadEntries->set($entries);
			}
		}

		protected function _automaticalyMarkAsRead() {
			if (!$this->CurrentUser->isLoggedIn() ||
					!$this->CurrentUser['user_automaticaly_mark_as_read']
			) {
				return;
			}

			if ($this->request->action === "index" &&
					!$this->Session->read('User.last_refresh_tmp')
			) {
				// initiate sessions last_refresh_tmp for new sessions
				$this->Session->write('User.last_refresh_tmp', time());
			}

			/* // old
			$isMarkAsReadRequest = $this->localReferer('controller') === 'entries' &&
					$this->localReferer('action') === 'index' &&
					$this->request->action === "index";
			*/

			$isMarkAsReadRequest = isset($this->request->query['mar']) &&
					$this->request->query['mar'] === '';

			if ($isMarkAsReadRequest &&
					$this->request->isPreview() === false
			) {
				// a second session A shall not accidentally mark something as read that isn't read on session B
				if ($this->Session->read('User.last_refresh_tmp') > $this->CurrentUser['last_refresh_unix']) {
					$this->CurrentUser->LastRefresh->set();
				}
				$this->Session->write('User.last_refresh_tmp', time());
				$this->redirect('/');
				return;
			} elseif ($this->request->action === "index") {
				$this->CurrentUser->LastRefresh->setMarker();
			}
		}

		protected function _prepareSlidetabData() {
			if ($this->CurrentUser->isLoggedIn()) {
				// get current user's recent entries for slidetab
				$this->set(
					'recentPosts',
					$this->Entry->getRecentEntries(
						$this->CurrentUser,
						[
							'user_id' => $this->CurrentUser->getId(),
							'limit' => 5
						]
					)
				);
				// get last 10 recent entries for slidetab
				$this->set(
					'recentEntries',
					$this->Entry->getRecentEntries($this->CurrentUser)
				);
				// get shouts
				if (in_array('slidetab_shoutbox', $this->viewVars['slidetabs'])) {
					$this->Shouts->setShoutsForView();
				}
			}
		}

		protected function _incrementViews($entry, $type = null) {
			if ($this->CurrentUser->isBot()) {
				return;
			}
			$cUserId = $this->CurrentUser->getId();

			if ($type === 'thread') {
				$this->Entry->threadIncrementViews($entry['Entry']['tid'], $cUserId);
			} elseif ($entry['Entry']['user_id'] != $cUserId) {
				$this->Entry->incrementViews($entry['Entry']['id']);
			}
		}

		protected function _setNotifications($newEntry) {
			if (isset($newEntry['Event'])) {
				$notis = [
					[
						'subject' => $newEntry['Entry']['id'],
						'event' => 'Model.Entry.replyToEntry',
						'receiver' => 'EmailNotification',
						'set' => $newEntry['Event'][1]['event_type_id'],
					],
					[
						'subject' => $newEntry['Entry']['tid'],
						'event' => 'Model.Entry.replyToThread',
						'receiver' => 'EmailNotification',
						'set' => $newEntry['Event'][2]['event_type_id'],
					]
				];
				$this->Entry->Esevent->notifyUserOnEvents(
					$newEntry['Entry']['user_id'],
					$notis
				);
			}
		}

		protected function _setAutoRefreshTime() {
			if (!$this->CurrentUser->isLoggedIn()) {
				return;
			}
			if ($this->CurrentUser['user_forum_refresh_time'] > 0) {
				$this->set(
					'autoPageReload',
					$this->CurrentUser['user_forum_refresh_time'] * 60
				);
			}
		}

		/**
		 * Gets thread ids for paginated entries/index.
		 *
		 * @param CurrentUserComponent $User
		 * @param array $order sort order
		 * @return array thread ids
		 */
		protected function _getInitialThreads(CurrentUserComponent $User, $order) {
			Stopwatch::start('Entries->_getInitialThreads() Paginate');

			$categories = $this->_setupCategoryChooser($User);

			//! Check DB performance after changing conditions/sorting!
			$this->paginate = [
				'conditions' => [
					'pid' => 0,
					'Entry.category_id' => $categories
				],
				'contain' => false,
				'fields' => 'id, pid, tid, time, last_answer, fixed',
				'limit' => Configure::read('Saito.Settings.topics_per_page'),
				'order' => $order,
				'getInitialThreads' => 1,
			];

			// disallows overwriting pagination params from request
			unset(
				$this->request->params['named']['direction'],
				$this->request->params['named']['sort']
			);
			$initialThreads = $this->paginate(null, null, array_keys($order));

			$initialThreadsNew = [];
			foreach ($initialThreads as $k => $v) {
				$initialThreadsNew[$k] = $v['Entry']['id'];
			}
			Stopwatch::stop('Entries->_getInitialThreads() Paginate');

			return $initialThreadsNew;
		}

		protected function _setupCategoryChooser(\Saito\User\ForumsUserInterface $User) {
			$categories = $User->Categories->getAllowed();

			$isUsed = $User->isLoggedIn() &&
					(
							Configure::read('Saito.Settings.category_chooser_global') ||
							(
									Configure::read(
										'Saito.Settings.category_chooser_user_override'
									) && $User['user_category_override']
							)
					);

			if ($isUsed) {
				// @todo find right place for this; also: User::getCategories();
				$UserCategories = new Categories($User->getSettings(), $categories);
				list($categories, $type, $custom) = $UserCategories->get();

				$this->set('categoryChooserChecked', $custom);

				switch ($type) {
					case 'single':
						$title = $User['user_category_active'];
						break;
					case 'custom':
						$title = __('Custom');
						break;
					default:
						$title = __('All Categories');
				}
				$this->set('categoryChooserTitleId', $title);
				$this->set('categoryChooser', $User->Categories->getAllowed('list'));
			}
			return $categories;
		}

		protected function _setAddViewVars() {
			//= categories for dropdown
			$categories = $this->CurrentUser->Categories->getAllowed('list');
			$this->set('categories', $categories);
		}

/**
 * Decide if an answering panel is show when rendering a posting
 */
		protected function _showAnsweringPanel() {
			$showAnsweringPanel = false;

			if ($this->CurrentUser->isLoggedIn()) {
				// Only logged in users see the answering buttons if they …
				if (// … directly on entries/view but not inline
						($this->request->action === 'view' && !$this->request->is('ajax'))
						// … directly in entries/mix
						|| $this->request->action === 'mix'
						// … inline viewing … on entries/index.
						|| ( $this->localReferer('controller') === 'entries' && $this->localReferer('action') === 'index')
				):
					$showAnsweringPanel = true;
				endif;
			}
			$this->set('showAnsweringPanel', $showAnsweringPanel);
		}

		protected function _setRootEntry($entry) {
			if ((int)$entry['Entry']['pid'] !== 0) {
				$_rootEntry = $this->Entry->find('first',
					[
						'contain' => false,
						'conditions' => ['Entry.id' => $entry['Entry']['tid']],
						'fields' => ['Entry.user_id']
					]);
			} else {
				$_rootEntry = $entry;
			}
			$this->set('rootEntry', $_rootEntry);
		}

		protected function _setTitleFromEntry($entry, $type = null) {
			if ($type === null) {
				$template =	__(':subject | :category');
			} else {
				$template =	__(':subject (:type) | :category');
			}
			$this->set('title_for_layout',
					CakeText::insert($template,
							[
									'category' => $entry['Category']['category'],
									'subject' => $entry['Entry']['subject'],
									'type' => $type
							]
					)
			);
		}

	}
