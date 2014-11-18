<?php

namespace App\Controller;

use App\Controller\Component\CurrentUserComponent;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Network\Exception\NotFoundException;
use Cake\Routing\RequestActionTrait;
use Saito\App\Registry;
use Saito\Posting\Posting;
use Saito\User\ForumsUserInterface;
use Stopwatch\Lib\Stopwatch;

class EntriesController extends AppController
{

    use RequestActionTrait;

    public $helpers = ['MarkitupEditor', 'Posting', 'Shouts', 'Text'];

    public $actionAuthConfig = [
        'ajax_toggle' => 'mod',
        'merge' => 'mod',
        'delete' => 'mod'
    ];

    /**
     * posting index
     *
     * @return void
     */
    public function index()
    {
        Stopwatch::start('Entries->index()');

        $this->_prepareSlidetabData();

        //= determine user sort order
        $sortKey = 'last_answer';
        if (!$this->CurrentUser['user_sort_last_answer']) {
            $sortKey = 'time';
        }
        $order = ['fixed' => 'DESC', $sortKey => 'DESC'];

        //= get threads
        $initials = $this->_getInitialThreads($this->CurrentUser, $order);
        $threads = $this->Entries->treesForThreads($initials, $order);
        $this->set('entries', $threads);

        $currentPage = (int)$this->request->query('page') || 1;
        if ($currentPage) {
            $this->set('title_for_layout', __('page') . ' ' . $currentPage);
        }
        if ($currentPage === 1
            && $this->CurrentUser->isLoggedIn()
            && $this->CurrentUser['user_automaticaly_mark_as_read']
        ) {
            $this->set('markAsRead', true);
        }
        // @bogus
        $this->request->session()->write('paginator.lastPage', $currentPage);
        $this->showDisclaimer = true;
        $this->set('allowThreadCollapse', true);
        $this->_showSlidetabs();

        $this->_setupCategoryChooser($this->CurrentUser);

        Stopwatch::stop('Entries->index()');
    }

    /**
     * Gets thread ids for paginated entries/index.
     *
     * @param CurrentUserComponent $User
     * @param array $order sort order
     * @return array thread ids
     */
    protected function _getInitialThreads(CurrentUserComponent $User, $order)
    {
        Stopwatch::start('Entries->_getInitialThreads() Paginate');

        $categories = $User->Categories->getCurrent('read');

        //! Check DB performance after changing conditions/sorting!
        $customFinderOptions = [
            'conditions' => [
                'Entries.category_id IN' => $categories
            ],
            'limit' => Configure::read('Saito.Settings.topics_per_page'),
            'order' => $order
        ];
        $this->paginate = [
            'finder' => ['indexPaginator' => $customFinderOptions],
        ];

        /* disallow sorting or ordering via request */
        $this->loadComponent('Paginator');
        // this is the only way to set the whitelist
        // loadComponent() or paginate() do not work
        $this->Paginator->config('whitelist', ['page'], false);
        $initialThreads = $this->paginate($this->Entries);

        $initialThreadsNew = [];
        foreach ($initialThreads as $k => $v) {
            $initialThreadsNew[$k] = $v['id'];
        }
        Stopwatch::stop('Entries->_getInitialThreads() Paginate');

        return $initialThreadsNew;
    }

    public function feed($type)
    {
        if (!$this->RequestHandler->isRss()) {
            throw new BadRequestException();
        }
        switch ($type) {
            case 'threads':
                $title = __('Last started threads');
                $order = 'time DESC';
                $conditions['pid'] = 0;
                break;
            default:
                $title = __('Last entries');
                $order = 'last_answer DESC';
        }
        $title = Configure::read('Saito.Settings.forum_name') . ' – ' . $title;
        $language = Configure::read('Saito.language');
        $this->set(compact('title', 'language'));

        $conditions['category_id IN'] = $this->CurrentUser->Categories->getAll(
            'read'
        );

        $entries = $this->Entries->find(
            'feed',
            [
                'conditions' => $conditions,
                'order' => $order
            ]
        );
        $this->set('entries', $entries);


        // serialize for JSON
        $this->set('_serialize', 'entries');
    }

    /**
     * Mix view
     *
     * @param $tid
     * @throws NotFoundException
     */
    public function mix($tid)
    {
        if (!$tid) {
            $this->redirect('/');

            return;
        }
        $postings = $this->Entries->treeForNode(
            $tid,
            ['root' => true, 'complete' => true]
        );

        if (empty($postings)) {
            $post = $this->Entries->find()
                                  ->select(['tid'])
                                  ->where(['id' => $tid])
                                  ->first();
            if (!empty($post)) {
                $this->redirect([$post->get('tid'), '#' => $tid], 301);

                return;
            }
            throw new NotFoundException;
        }

        // check if anonymous tries to access internal categories
        $root = $postings;
        $resource = 'saito.core.category.' . $root->get(
                'category'
            )['id'] . '.read';
        if (!$this->CurrentUser->permission($resource)) {
            $this->_requireAuth();

            return;
        }

        $this->_setRootEntry($root);
        $this->Title->setFromPosting($root, __('view.type.mix'));

        $this->set('entries', $postings);

        $this->_showAnsweringPanel();

        $this->_incrementViews($root, 'thread');

        $this->_marMixThread = $tid;
    }

    /**
     * load front page force all entries mark-as-read
     */
    public function update()
    {
        $this->autoRender = false;
        $this->CurrentUser->LastRefresh->set('now');
        $this->redirect('/entries/index');
    }

    /**
     * Outputs raw markup of an posting $id
     *
     * @param int $id
     */
    public function source($id = null)
    {
        $this->autoLayout = false;
        $this->view($id);
    }

    public function view($id = null)
    {
        Stopwatch::start('Entries->view()');

        // redirect if no id is given
        if (!$id) {
            $this->Flash->set(__('Invalid post'), ['element' => 'error']);

            return $this->redirect(['action' => 'index']);
        }

        $entry = $this->Entries->get($id);

        // redirect if posting doesn't exists
        if ($entry == false) {
            $this->Flash->set(__('Invalid post'));
            $this->redirect('/');

            return;
        }

        // check if anonymous tries to access internal categories
        $resource = 'saito.core.category.' . $entry->get(
                'category'
            )['id'] . '.read';
        if (!$this->CurrentUser->permission($resource)) {
            $this->_requireAuth();

            return;
        }

        $this->set('entry', $entry);
        $this->_incrementViews($entry);
        $this->_setRootEntry($entry);
        $this->_showAnsweringPanel();

        $this->CurrentUser->ReadEntries->set($entry);

        // inline open
        if ($this->request->is('ajax')) {
            $this->render('/Element/entry/view_posting');

            return;
        }

        // full page request
        $this->set(
            'tree',
            $this->Entries->treeForNode($entry->get('tid'), ['root' => true])
        );
        $this->Title->setFromPosting($entry);

        Stopwatch::stop('Entries->view()');
    }

    /**
     * @param null $id
     *
     * @return string
     * @throws ForbiddenException
     */
    public function add($id = null)
    {
        $title = __('Write a New Entry');

        if (!empty($this->request->data)) {
            //= insert new posting
            $posting = $this->Entries->createPosting($this->request->data());

            // inserting new posting was successful
            if ($posting !== false) {
                // @todo 3.0
//					$this->_setNotifications($newPosting + $this->request->data);

                $id = $posting->get('id');
                $pid = $posting->get('pid');
                $tid = $posting->get('tid');

                if ($this->request->is('ajax')) {
                    if ($this->localReferer('action') === 'index') {
                        //= inline answer
                        $json = json_encode(
                            ['id' => $id, 'pid' => $pid, 'tid' => $id]
                        );
                        $this->response->type('json');
                        $this->response->body($json);
                    }

                    return $this->response;
                } else {
                    //= answering through POST request
                    $url = ['controller' => 'entries'];
                    if ($this->localReferer('action') === 'mix') {
                        //= answer came from mix-view
                        $url += ['action' => 'mix', $tid, '#' => $id];
                    } else {
                        //= normal posting from entries/add or entries/view
                        $url += ['action' => 'view', $posting->get('id')];
                    }
                    $this->redirect($url);

                    return;
                }
            } else {
                //= Error while trying to save a post
                $posting = $this->Entries->newEntity($this->request->data());

                if (count($posting->errors()) === 0) {
                    //= Error isn't displayed as form validation error.
                    $this->Flash->set(
                        __(
                            'Something clogged the tubes. Could not save entry. Try again.'
                        ),
                        ['element' => 'error']
                    );
                }
            }
        } else {
            //= show form
            $posting = $this->Entries->newEntity();
            $isAnswer = $id !== null;

            if ($isAnswer) {
                //= answer to existing posting
                if ($this->request->is('ajax') === false) {
                    $this->redirect($this->referer());

                    return;
                }

                $parent = $this->Entries->get($id);

                if ($parent->isAnsweringForbidden()) {
                    throw new ForbiddenException;
                }

                $this->set('citeSubject', $parent->get('subject'));
                $this->set('citeText', $parent->get('text'));

                $posting = $this->Entries->patchEntity(
                    $posting,
                    ['pid' => $id]
                );

                /*
                 * @todo 3.0
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
                 */

                // set Subnav
                $headerSubnavLeftTitle = __(
                    'back_to_posting_from_linkname',
                    $parent->get('User')['username']
                );
                $this->set('headerSubnavLeftTitle', $headerSubnavLeftTitle);
                $title = __('Write a Reply');
            } else {
                // new posting which creates new thread
                $posting = $this->Entries->patchEntity(
                    $posting,
                    ['pid' => 0, 'tid' => 0]
                );
            }
        }

        $isInline = $isAnswer = !$posting->isRoot();
        $formId = $posting->get('pid');

        $this->set(
            compact('isAnswer', 'isInline', 'formId', 'posting', 'title')
        );
        $this->setAddViewVars($isAnswer);
    }

    public function threadLine($id = null)
    {
        $this->set('entry_sub', $this->Entry->read(null, $id));
        // ajax requests so far are always answers
        $this->set('level', '1');
    }

    /**
     * @param null $id
     *
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function edit($id = null)
    {
        if (empty($id)) {
            throw new BadRequestException;
        }

        $posting = $this->Entries->get($id, ['return' => 'Entity']);
        if (!$posting) {
            throw new NotFoundException;
        }

        switch ($posting->toPosting()->isEditingAsCurrentUserForbidden()) {
            case 'time':
                $this->Flash->set(
                    'Stand by your word bro\', it\'s too late. @lo',
                    ['element' => 'error']
                );
                $this->redirect(['action' => 'view', $id]);

                return;
            case 'user':
                $this->Flash->set(
                    'Not your horse, Hoss! @lo',
                    ['element' => 'error']
                );
                $this->redirect(['action' => 'view', $id]);

                return;
            case true:
                $this->Flash->set(
                    'Something went terribly wrong. Alert the authorities now! @lo',
                    ['element' => 'error']
                );

                return;
        }

        // try to save edit
        $data = $this->request->data();
        if (!empty($data)) {
            $data['id'] = $posting->get('id');
            $newEntry = $this->Entries->update($posting, $data);
            if ($newEntry) {
                /* @todo 3.0 Esevent
                 * $this->_setNotifications(am($this->request['data'], $posting));
                 */
                $this->redirect(['action' => 'view', $id]);

                return;
            } else {
                $this->Flash->set(
                    __(
                        'Something clogged the tubes. Could not save entry. Try again.'
                    ),
                    ['element' => 'warning']
                );
            }
        }

        // show editing form
        if ($posting->toPosting()->isEditingWithRoleUserForbidden()) {
            $this->Flash->set(
                __('notice_you_are_editing_as_mod'),
                ['element' => 'warning']
            );
        }

        $this->Entries->patchEntity($posting, $this->request->data());

        // get text of parent entry for citation
        $parentEntryId = $posting->get('pid');
        if ($parentEntryId > 0) {
            $parentEntry = $this->Entries->get($parentEntryId);
            $this->set('citeText', $parentEntry->get('text'));
        }

        // get notifications
        /* @todo 3.0 Esevent
         * $notis = $this->Entry->Esevent->checkEventsForUser(
         * $posting['Entry']['user_id'],
         * array(
         * array(
         * 'subject' => $posting['Entry']['id'],
         * 'event' => 'Model.Entry.replyToEntry',
         * 'receiver' => 'EmailNotification',
         * ),
         * array(
         * 'subject' => $posting['Entry']['tid'],
         * 'event' => 'Model.Entry.replyToThread',
         * 'receiver' => 'EmailNotification',
         * ),
         * )
         * );
         * $this->set('notis', $notis);
         * */

        $isAnswer = !$posting;
        $isInline = false;
        $formId = $posting->get('pid');

        $this->set(compact('isAnswer', 'isInline', 'formId', 'posting'));

        // set headers
        $this->set(
            'headerSubnavLeftTitle',
            __(
                'back_to_posting_from_linkname',
                $posting->get('user')->get('username')
            )
        );
        $this->set('headerSubnavLeftUrl', ['action' => 'view', $id]);
        $this->set('form_title', __('edit_linkname'));
        $this->setAddViewVars($isAnswer);
        $this->render('/Entries/add');
    }

    /**
     * Delete posting
     *
     * @param null $id
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        if (!$id) {
            throw new NotFoundException;
        }
        $posting = $this->Entries->get($id);
        if (!$posting) {
            throw new NotFoundException;
        }

        $success = $this->Entries->treeDeleteNode($id);

        if ($success) {
            $flashType = 'success';
            if ($posting->isRoot()) {
                $message = __('delete_tree_success');
                $redirect = '/';
            } else {
                $message = __('delete_subtree_success');
                $redirect = '/entries/view/' . $posting->get('pid');
            }
        } else {
            $flashType = 'error';
            $message = __('delete_tree_error');
            $redirect = $this->referer();
        }
        $this->Flash->set($message, ['element' => $flashType]);
        $this->redirect($redirect);
    }

    /**
     * Empty function for benchmarking
     */
    public function e()
    {
        Stopwatch::start('Entries->e()');
        Stopwatch::stop('Entries->e()');
    }

    /**
     * Marks sub-entry $id as solution to its current root-entry
     *
     * @param $id
     * @throws BadRequestException
     */
    public function solve($id)
    {
        $this->autoRender = false;
        try {
            $success = $this->Entries->toggleSolve($id);
            if (!$success) {
                throw new BadRequestException;
            }
        } catch (\Exception $e) {
            throw new BadRequestException;
        }
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function preview()
    {
        if (!$this->request->is('post') || !$this->request->is('ajax')) {
            throw new BadRequestException(null, 1434128359);
        }

        $data = $this->request->data();
        $newEntry = [
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
            'time' => bDate()
        ];
        $this->Entries->prepare($newEntry);

        $validator = $this->Entries->validator();
        $errors = $validator->errors($newEntry);

        if (empty($errors)) {
            // no validation errors
            $newEntry['user'] = $this->CurrentUser->getSettings();
            $newEntry['category'] = $this->Entries->Categories->find()
                ->where(['id' => $newEntry['category_id']])
                ->first();
            $posting = Registry::newInstance(
                '\Saito\Posting\Posting',
                ['rawData' => $newEntry]
            );
            $this->set(compact('posting'));
        } else {
            // validation errors
            foreach ($errors as $field => $error) {
                $message = __d('nondynamic', $field) . ": " . __d( 'nondynamic', $error[0]);
                $this->JsData->addAppJsMessage(
                    $message,
                    [
                        'type' => 'error',
                        'channel' => 'form',
                        'element' => '#Entry' . ucfirst($field)
                    ]
                );
            }
            $this->autoRender = false;

            $this->response->type('json');
//            return json_encode($this->JsData->getAppJsMessages());
        }
    }

    /**
     * @param null $sourceId
     *
     * @throws NotFoundException
     * // @todo put into admin entries controller
     */
    public function merge($sourceId = null)
    {
        if (!$sourceId) {
            throw new NotFoundException();
        }

        $posting = $this->Entries->findById($sourceId)->first();

        if (!$posting || !$posting->isRoot()) {
            throw new NotFoundException();
        }

        // perform move operation
        $targetId = $this->request->data('targetId');
        if (!empty($targetId)) {
            if ($this->Entries->threadMerge($sourceId, $targetId)) {
                $this->redirect('/entries/view/' . $sourceId);

                return;
            } else {
                $this->Flash->set(__('Error'), ['element' => 'error']);
            }
        }

        $this->layout = 'admin';
        $this->set(compact('posting'));
    }

    /**
     *
     *
     * @param null $id
     * @param null $toggle
     *
     * @return \Cake\Network\Response
     */
    public function ajax_toggle($id = null, $toggle = null)
    {
        $allowed = ['fixed', 'locked'];
        if (!$id
            || !$toggle
            || !$this->request->is('ajax')
            || !in_array($toggle, $allowed)
        ) {
            throw new BadRequestException;
        }

        $current = $this->Entries->toggle((int)$id, $toggle);
        if ($current) {
            $out['html'] = __d('nondynamic', $toggle . '_unset_entry_link');
        } else {
            $out['html'] = __d('nondynamic', $toggle . '_set_entry_link');
        }

        $this->response->type('json');
        $this->response->body(json_encode($out));

        return $this->response;
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        Stopwatch::start('Entries->beforeFilter()');

        $this->_automaticalyMarkAsRead();

        $this->Security->config(
            'unlockedActions',
            ['preview', 'solve', 'view']
        );
        $this->Auth->allow(['feed', 'index', 'view', 'mix', 'update']);

        if ($this->request->action === 'index') {
            $this->setAutoRefreshTime();
        }

        Stopwatch::stop('Entries->beforeFilter()');
    }

    /**
     * {@inheritdoc}
     */
    public function afterFilter(Event $event)
    {
        parent::afterFilter($event);
        // @todo @performance extract all ids from Posting object in action,
        // pass them to here, remove find() call her
        if (isset($this->_marMixThread)) {
            $entries = $this->Entries->find()
                ->select(['id', 'time'])
                ->where(['tid' => $this->_marMixThread]);
            $this->CurrentUser->ReadEntries->set($entries);
        }
    }

    /**
     * automatic mark-as-read
     *
     * @return void
     */
    protected function _automaticalyMarkAsRead()
    {
        if (!$this->CurrentUser->isLoggedIn() ||
            !$this->CurrentUser['user_automaticaly_mark_as_read']
        ) {
            return;
        }

        if ($this->request->action === 'index' &&
            !$this->request->session()->read('User.last_refresh_tmp')
        ) {
            // initiate sessions last_refresh_tmp for new sessions
            $this->request->session()->write('User.last_refresh_tmp', time());
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
            $lastRefreshTemp = $this->request->session()
                ->read('User.last_refresh_tmp');
            if ($lastRefreshTemp > $this->CurrentUser['last_refresh_unix']) {
                $this->CurrentUser->LastRefresh->set();
            }
            $this->request->session()->write('User.last_refresh_tmp', time());
            $this->redirect('/');

            return;
        } elseif ($this->request->action === "index") {
            $this->CurrentUser->LastRefresh->setMarker();
        }
    }

    protected function _prepareSlidetabData()
    {
        if ($this->CurrentUser->isLoggedIn()) {
            // @todo 3.0
            /*
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
            */
        }
    }

    protected function _incrementViews($entry, $type = null)
    {
        if ($this->CurrentUser->isBot()) {
            return;
        }
        $cUserId = $this->CurrentUser->getId();

        if ($type === 'thread') {
            $this->Entries->threadIncrementViews($entry->get('tid'), $cUserId);
        } elseif ($entry->get('user_id') !== $cUserId) {
            $this->Entries->incrementViews($entry->get('id'));
        }
    }

    protected function _setNotifications($newEntry)
    {
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

    /**
     * set auto refresh time
     *
     * @return void
     */
    protected function setAutoRefreshTime()
    {
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
     * set view vars for category chooser
     *
     * @param ForumsUserInterface $User CurrentUser
     * @return void
     */
    protected function _setupCategoryChooser(ForumsUserInterface $User)
    {
        if (!$User->isLoggedIn()) {
            return;
        }
        $globalActivation = Configure::read(
            'Saito.Settings.category_chooser_global'
        );
        if (!$globalActivation) {
            if (!Configure::read(
                'Saito.Settings.category_chooser_user_override'
            )
            ) {
                return;
            }
            if (!$User->get('user_category_override')) {
                return;
            }
        }

        $this->set(
            'categoryChooserChecked',
            $User->Categories->getCustom('read')
        );
        switch ($User->Categories->getType()) {
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
        $this->set(
            'categoryChooser',
            $User->Categories->getAll('read', 'list')
        );
    }

    /**
     * set additional vars for creating a new posting
     *
     * @param bool $isAnswer is new posting answer or root
     * @return void
     */
    protected function setAddViewVars($isAnswer)
    {
        //= categories for dropdown
        $action = $isAnswer ? 'answer' : 'thread';
        $categories = $this->CurrentUser->Categories->getAll($action, 'list');
        $this->set('categories', $categories);
    }

    /**
     * Decide if an answering panel is show when rendering a posting
     *
     * @return void
     */
    protected function _showAnsweringPanel()
    {
        $showAnsweringPanel = false;

        if ($this->CurrentUser->isLoggedIn()) {
            // Only logged in users see the answering buttons if they …
            if (// … directly on entries/view but not inline
                ($this->request->action === 'view' && !$this->request->is('ajax'))
                // … directly in entries/mix
                || $this->request->action === 'mix'
                // … inline viewing … on entries/index.
                || ($this->localReferer('controller') === 'entries'
                    && $this->localReferer('action') === 'index')
            ) {
                $showAnsweringPanel = true;
            }
        }
        $this->set('showAnsweringPanel', $showAnsweringPanel);
    }

    /**
     * makes root posting of $posting avaiable in view
     *
     * @param Posting $posting posting for root entry
     * @return void
     */
    protected function _setRootEntry(Posting $posting)
    {
        if (!$posting->isRoot()) {
            $root = $this->Entries->find()
                ->select(['user_id'])
                ->where(['id' => $posting->get('tid')])
                ->first();
        } else {
            $root = $posting;
        }
        $this->set('rootEntry', $root);
    }
}
