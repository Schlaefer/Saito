<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use App\Controller\Component\CurrentUserComponent;
use App\Model\Entity\Entry;
use App\Model\Table\EntriesTable;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Http\Response;
use Cake\Routing\RequestActionTrait;
use Cake\View\Helper\IdGeneratorTrait;
use Saito\App\Registry;
use Saito\Posting\Posting;
use Saito\User\ForumsUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Class EntriesController
 *
 * @property EntriesTable $Entries
 * @package App\Controller
 */
class EntriesController extends AppController
{
    use IdGeneratorTrait;
    use RequestActionTrait;

    public $helpers = ['MarkitupEditor', 'Posting', 'Shouts', 'Text'];

    public $actionAuthConfig = [
        'ajaxToggle' => 'mod',
        'merge' => 'mod',
        'delete' => 'mod'
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Referer');
        $this->loadComponent('MarkAsRead');
        $this->loadComponent('Threads');
    }

    /**
     * posting index
     *
     * @return void|\Cake\Network\Response
     */
    public function index()
    {
        Stopwatch::start('Entries->index()');

        //= determine user sort order
        $sortKey = 'last_answer';
        if (!$this->CurrentUser->get('user_sort_last_answer')) {
            $sortKey = 'time';
        }
        $order = ['fixed' => 'DESC', $sortKey => 'DESC'];

        //= get threads
        $threads = $this->Threads->paginate($order);
        $this->set('entries', $threads);

        $currentPage = (int)$this->request->query('page') ?: 1;
        if ($currentPage > 1) {
            $this->set('titleForLayout', __('page') . ' ' . $currentPage);
        }
        if ($currentPage === 1) {
            if ($this->MarkAsRead->refresh()) {
                return $this->redirect(['action' => 'index']);
            }
            $this->MarkAsRead->next();
        }

        // @bogus
        $this->request->session()->write('paginator.lastPage', $currentPage);
        $this->showDisclaimer = true;
        $this->set('allowThreadCollapse', true);
        $this->Slidetabs->show();

        $this->_setupCategoryChooser($this->CurrentUser);

        $this->loadComponent('AutoReload');
        $this->AutoReload->after($this->CurrentUser);

        Stopwatch::stop('Entries->index()');
    }

    /**
     * RSS-feed for postings.
     *
     * @param string $type feed-content
     * @return void
     */
    public function feed($type)
    {
        if (!$this->RequestHandler->isRss()) {
            throw new BadRequestException();
        }
        switch ($type) {
            case 'threads':
                $title = __('Last started threads');
                $order = ['time' => 'DESC'];
                $conditions['pid'] = 0;
                break;
            default:
                $title = __('Last entries');
                $order = ['last_answer' => 'DESC'];
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
     * @param string $tid thread-ID
     * @return void|Response
     * @throws NotFoundException
     */
    public function mix($tid)
    {
        if (!$tid) {
            return $this->redirect('/');
        }
        $postings = $this->Entries->treeForNode(
            $tid,
            ['root' => true, 'complete' => true]
        );

        if (empty($postings)) {
            /* @var $post Entry */
            $post = $this->Entries->find()
                ->select(['tid'])
                ->where(['id' => $tid])
                ->first();
            if (!empty($post)) {
                return $this->redirect([$post->get('tid'), '#' => $tid], 301);
            }
            throw new NotFoundException;
        }

        // check if anonymous tries to access internal categories
        $root = $postings;
        if (!$this->CurrentUser->Categories->permission('read', $root->get('category'))) {
            return $this->_requireAuth();
        }

        $this->_setRootEntry($root);
        $this->Title->setFromPosting($root, __('view.type.mix'));

        $this->set('entries', $postings);

        $this->_showAnsweringPanel();

        $this->Threads->incrementViews($root, 'thread');
        $this->MarkAsRead->thread($postings);
    }

    /**
     * load front page force all entries mark-as-read
     *
     * @return void
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
     * @param string $id posting-ID
     * @return void
     */
    public function source($id = null)
    {
        $this->viewBuilder()->enableAutoLayout(false);
        $this->view($id);
    }

    /**
     * View posting.
     *
     * @param string $id posting-ID
     * @return \Cake\Network\Response|void
     */
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

            return $this->redirect('/');
        }

        if (!$this->CurrentUser->Categories->permission('read', $entry->get('category'))) {
            return $this->_requireAuth();
        }

        $this->set('entry', $entry);
        $this->Threads->incrementViews($entry);
        $this->_setRootEntry($entry);
        $this->_showAnsweringPanel();

        $this->CurrentUser->ReadEntries->set($entry);

        // inline open
        if ($this->request->is('ajax')) {
            return $this->render('/Element/entry/view_posting');
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
     * Add new posting.
     *
     * @param null|string $id parent-ID if is answer
     * @return void|\Cake\Network\Response
     * @throws ForbiddenException
     */
    public function add($id = null)
    {
        $title = __('Write a New Entry');

        if (!empty($this->request->data)) {
            //= insert new posting
            $posting = $this->Entries->createPosting($this->request->data());

            // inserting new posting was successful
            if ($posting !== false && !count($posting->errors())) {
                // @td 3.0 Notif
                //$this->_setNotifications($newPosting + $this->request->data);

                $id = $posting->get('id');
                $pid = $posting->get('pid');
                $tid = $posting->get('tid');

                if ($this->request->is('ajax')) {
                    if ($this->Referer->wasAction('index')) {
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
                    if ($this->Referer->wasAction('mix')) {
                        //= answer came from mix-view
                        $url += ['action' => 'mix', $tid, '#' => $id];
                    } else {
                        //= normal posting from entries/add or entries/view
                        $url += ['action' => 'view', $posting->get('id')];
                    }

                    return $this->redirect($url);
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
                    return $this->redirect($this->referer());
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
                 * @td 3.0 Notif
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
                    $parent->get('user')->get('username')
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
        $this->_setAddViewVars($isAnswer);
    }

    /**
     * Get thread-line to insert after an inline-answer
     *
     * @param string $id posting-ID
     * @return void|\Cake\Network\Response
     */
    public function threadLine($id = null)
    {
        $posting = $this->Entries->get($id);
        if (!$this->CurrentUser->Categories->permission('read', $posting->get('category'))) {
            return $this->_requireAuth();
        }

        $this->set('entrySub', $posting);
        // ajax requests so far are always answers
        $this->response->type('json');
        $this->set('level', '1');
    }

    /**
     * Edit posting
     *
     * @param string $id posting-ID
     * @return void|\Cake\Network\Response
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

                return $this->redirect(['action' => 'view', $id]);
            case 'user':
                $this->Flash->set(
                    'Not your horse, Hoss! @lo',
                    ['element' => 'error']
                );

                return $this->redirect(['action' => 'view', $id]);
            case true:
                $this->Flash->set(
                    'Something went terribly wrong. Alert the authorities now! @lo',
                    ['element' => 'error']
                );

                return $this->redirect(['action' => 'view', $id]);
        }

        // try to save edit
        $data = $this->request->data();
        if (!empty($data)) {
            $data['id'] = $posting->get('id');
            $newEntry = $this->Entries->update($posting, $data);
            if ($newEntry) {
                /* @td 3.0 Notif
                 * $this->_setNotifications(am($this->request['data'], $posting));
                 */
                return $this->redirect(['action' => 'view', $id]);
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
        /* @td 3.0 Notif
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
        $this->_setAddViewVars($isAnswer);
        $this->render('/Entries/add');
    }

    /**
     * Delete posting
     *
     * @param string $id posting-ID
     * @return void
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    public function delete($id = null)
    {
        //$this->request->allowMethod(['post', 'delete']);
        if (!$id) {
            throw new NotFoundException;
        }
        /* @var Entry $posting */
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
     *
     * @return void
     */
    public function e()
    {
        Stopwatch::start('Entries->e()');
        Stopwatch::stop('Entries->e()');
    }

    /**
     * Marks sub-entry $id as solution to its current root-entry
     *
     * @param string $id posting-ID
     * @return void
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
     * Generate posting preview for JSON frontend.
     *
     * @return \Cake\Network\Response|void
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
            'time' => new Time()
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
                $message = __d('nondynamic', $field) . ": " . __d('nondynamic', current($error));
                $this->JsData->addAppJsMessage(
                    $message,
                    [
                        'type' => 'error',
                        'channel' => 'form',
                        'element' => '#' . $this->_domId($field)
                    ]
                );
            }
            $this->autoRender = false;

            $this->response->type('json');
            $body = json_encode($this->JsData->getAppJsMessages());
            $this->response->body($body);

            return $this->response;
        }
    }

    /**
     * Merge threads.
     *
     * @param string $sourceId posting-ID of thread to be merged
     * @return void
     * @throws NotFoundException
     * @td put into admin entries controller
     */
    public function merge($sourceId = null)
    {
        if (!$sourceId) {
            throw new NotFoundException();
        }

        /* @var Entry */
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

        $this->viewBuilder()->layout('admin');
        $this->set(compact('posting'));
    }

    /**
     * Toggle posting property via ajax request.
     *
     * @param string $id posting-ID
     * @param string $toggle property
     *
     * @return \Cake\Network\Response
     */
    public function ajaxToggle($id = null, $toggle = null)
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

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        Stopwatch::start('Entries->beforeFilter()');

        $this->Security->config(
            'unlockedActions',
            ['preview', 'solve', 'view']
        );
        $this->Auth->allow(['feed', 'index', 'view', 'mix', 'update']);

        Stopwatch::stop('Entries->beforeFilter()');
    }

    /**
     * Set notifications from new posting.
     *
     * @param Entity $newEntry posting
     * @return void
     */
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
                $title = $User->get('user_category_active');
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
    protected function _setAddViewVars($isAnswer)
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
                || ($this->Referer->wasController('entries')
                    && $this->Referer->wasAction('index'))
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
