<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Saito\Exception\SaitoForbiddenException;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\Permission\ResourceAI;
use Stopwatch\Lib\Stopwatch;

/**
 * Class EntriesController
 *
 * @property \Saito\User\CurrentUser\CurrentUserInterface $CurrentUser
 * @property \App\Model\Table\EntriesTable $Entries
 * @property \App\Controller\Component\MarkAsReadComponent $MarkAsRead
 * @property \App\Controller\Component\PostingComponent $Posting
 * @property \App\Controller\Component\RefererComponent $Referer
 * @property \App\Controller\Component\ThreadsComponent $Threads
 */
class EntriesController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Posting');
        $this->loadComponent('MarkAsRead');
        $this->loadComponent('Referer');
        $this->loadComponent('Threads', ['table' => $this->Entries]);
    }

    /**
     * posting index
     *
     * @return void|\Cake\Http\Response
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
        $threads = $this->Threads->paginate($order, $this->CurrentUser);
        $this->set('entries', $threads);

        $currentPage = (int)$this->request->getQuery('page') ?: 1;
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
        $this->request->getSession()->write('paginator.lastPage', $currentPage);
        $this->set('showDisclaimer', true);
        $this->set('showBottomNavigation', true);
        $this->set('allowThreadCollapse', true);
        $this->Slidetabs->show();

        $this->_setupCategoryChooser($this->CurrentUser);

        /** @var \App\Controller\Component\AutoReloadComponent $autoReload */
        $autoReload = $this->loadComponent('AutoReload');
        $autoReload->after($this->CurrentUser);

        Stopwatch::stop('Entries->index()');
    }

    /**
     * Mix view
     *
     * @param string $tid thread-ID
     * @return void|\Cake\Http\Response
     * @throws \Cake\Http\Exception\NotFoundException
     */
    public function mix($tid)
    {
        $tid = (int)$tid;
        if ($tid <= 0) {
            throw new BadRequestException();
        }

        try {
            $postings = $this->Entries->postingsForThread($tid, true, $this->CurrentUser);
        } catch (RecordNotFoundException $e) {
            /// redirect sub-posting to mix view of thread
            $actualTid = $this->Entries->getThreadId($tid);

            return $this->redirect([$actualTid, '#' => $tid], 301);
        }

        // check if anonymous tries to access internal categories
        $root = $postings;
        if (!$this->CurrentUser->getCategories()->permission('read', $root->get('category'))) {
            return $this->_requireAuth();
        }

        $this->_setRootEntry($root);
        $this->Title->setFromPosting($root, __('view.type.mix'));

        $this->set('showBottomNavigation', true);
        $this->set('entries', $postings);

        $this->_showAnsweringPanel();

        $this->Threads->incrementViewsForThread($root, $this->CurrentUser);
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
        $this->CurrentUser->getLastRefresh()->set();
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
     * @return \Cake\Http\Response|void
     */
    public function view(string $id)
    {
        $id = (int)$id;
        Stopwatch::start('Entries->view()');

        $entry = $this->Entries->get($id);
        $posting = $entry->toPosting()->withCurrentUser($this->CurrentUser);

        if (!$this->CurrentUser->getCategories()->permission('read', $posting->get('category'))) {
            return $this->_requireAuth();
        }

        $this->set('entry', $posting);
        $this->Threads->incrementViewsForPosting($posting, $this->CurrentUser);
        $this->_setRootEntry($posting);
        $this->_showAnsweringPanel();

        $this->MarkAsRead->posting($posting);

        // inline open
        if ($this->request->is('ajax')) {
            return $this->render('/element/entry/view_posting');
        }

        // full page request
        $this->set(
            'tree',
            $this->Entries->postingsForThread($posting->get('tid'), false, $this->CurrentUser)
        );
        $this->Title->setFromPosting($posting);

        Stopwatch::stop('Entries->view()');
    }

    /**
     * Add new posting.
     *
     * @return void|\Cake\Http\Response
     */
    public function add()
    {
        $titleForPage = __('Write a New Posting');
        $this->set(compact('titleForPage'));
    }

    /**
     * Edit posting
     *
     * @param string $id posting-ID
     * @return void|\Cake\Http\Response
     * @throws \Cake\Http\Exception\NotFoundException
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function edit(string $id)
    {
        $id = (int)$id;
        $entry = $this->Entries->get($id);
        $posting = $entry->toPosting()->withCurrentUser($this->CurrentUser);

        if (!$posting->isEditingAllowed()) {
            throw new SaitoForbiddenException(
                'Access to posting in EntriesController:edit() forbidden.',
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        // show editing form
        if (!$posting->isEditingAsUserAllowed()) {
            $this->Flash->set(
                __('notice_you_are_editing_as_mod'),
                ['element' => 'warning']
            );
        }

        $this->set(compact('posting'));

        // set headers
        $this->set(
            'headerSubnavLeftTitle',
            __('back_to_posting_from_linkname', $posting->get('name'))
        );
        $this->set('headerSubnavLeftUrl', ['action' => 'view', $id]);
        $this->set('form_title', __('edit_linkname'));
        $this->render('/Entries/add');
    }

    /**
     * Get thread-line to insert after an inline-answer
     *
     * @param string $id posting-ID
     * @return void|\Cake\Http\Response
     */
    public function threadline($id = null)
    {
        $posting = $this->Entries->get($id)->toPosting()->withCurrentUser($this->CurrentUser);
        if (!$this->CurrentUser->getCategories()->permission('read', $posting->get('category'))) {
            return $this->_requireAuth();
        }

        $this->set('entrySub', $posting);
        // ajax requests so far are always answers
        $this->response = $this->response->withType('json');
        $this->set('level', '1');
    }

    /**
     * Delete posting
     *
     * @param string $id posting-ID
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException
     * @throws \Cake\Http\Exception\MethodNotAllowedException
     */
    public function delete(string $id)
    {
        //$this->request->allowMethod(['post', 'delete']);
        $id = (int)$id;
        if (!$id) {
            throw new NotFoundException();
        }
        /** @var \App\Model\Entity\Entry $posting */
        $posting = $this->Entries->get($id);

        $action = $posting->isRoot() ? 'thread' : 'answer';
        $allowed = $this->CurrentUser->getCategories()
            ->permission($action, $posting->get('category_id'));
        if (!$allowed) {
            throw new SaitoForbiddenException();
        }

        $success = $this->Entries->deletePosting($id);

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
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function solve($id)
    {
        $this->autoRender = false;
        try {
            $posting = $this->Entries->get($id);

            if (empty($posting)) {
                throw new \InvalidArgumentException('Posting to mark solved not found.');
            }

            $rootId = $posting->get('tid');
            $rootPosting = $this->Entries->get($rootId);

            $allowed = $this->CurrentUser->permission(
                'saito.core.posting.solves.set',
                (new ResourceAI())->onRole($rootPosting->get('user')->getRole())->onOwner($rootPosting->get('user_id'))
            );
            if (!$allowed) {
                throw new SaitoForbiddenException(
                    sprintf('Attempt to mark posting %s as solution.', $posting->get('id')),
                    ['CurrentUser' => $this->CurrentUser]
                );
            }

            $value = $posting->get('solves') ? 0 : $rootPosting->get('tid');
            $success = $this->Entries->updateEntry($posting, ['solves' => $value]);

            if (!$success) {
                throw new BadRequestException();
            }
        } catch (\Exception $e) {
            throw new BadRequestException();
        }
    }

    /**
     * Merge threads.
     *
     * @param string $sourceId posting-ID of thread to be merged
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException
     * @td put into admin entries controller
     */
    public function merge(?string $sourceId = null)
    {
        $sourceId = (int)$sourceId;
        if (empty($sourceId)) {
            throw new NotFoundException();
        }

        $entry = $this->Entries->get($sourceId);

        if (!$entry->isRoot()) {
            throw new NotFoundException();
        }

        // perform move operation
        $targetId = $this->request->getData('targetId');
        if (!empty($targetId)) {
            if ($this->Entries->threadMerge($sourceId, $targetId)) {
                $this->redirect('/entries/view/' . $sourceId);

                return;
            } else {
                $this->Flash->set(__('Error'), ['element' => 'error']);
            }
        }

        $this->viewBuilder()->setLayout('Admin.admin');
        $this->set('posting', $entry);
    }

    /**
     * Toggle posting property via ajax request.
     *
     * @param string $id posting-ID
     * @param string $toggle property
     *
     * @return \Cake\Http\Response
     */
    public function ajaxToggle($id = null, $toggle = null)
    {
        $allowed = ['fixed', 'locked'];
        if (
            !$id
            || !$toggle
            || !$this->request->is('ajax')
            || !in_array($toggle, $allowed)
        ) {
            throw new BadRequestException();
        }

        $posting = $this->Entries->get($id);
        $data = ['id' => (int)$id, $toggle => !$posting->get($toggle)];
        $this->Posting->update($posting, $data, $this->CurrentUser);

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody(json_encode('OK'));

        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        Stopwatch::start('Entries->beforeFilter()');

        $this->Security->setConfig(
            'unlockedActions',
            ['solve', 'view']
        );
        $this->Authentication->allowUnauthenticated(['index', 'view', 'mix', 'update']);

        $this->AuthUser->authorizeAction('ajaxToggle', 'saito.core.posting.pinAndLock');
        $this->AuthUser->authorizeAction('merge', 'saito.core.posting.merge');
        $this->AuthUser->authorizeAction('delete', 'saito.core.posting.delete');

        Stopwatch::stop('Entries->beforeFilter()');
    }

    /**
     * set view vars for category chooser
     *
     * @param \Saito\User\CurrentUser\CurrentUserInterface $User CurrentUser
     * @return void
     */
    protected function _setupCategoryChooser(CurrentUserInterface $User)
    {
        if (!$User->isLoggedIn()) {
            return;
        }
        $globalActivation = Configure::read(
            'Saito.Settings.category_chooser_global'
        );
        if (!$globalActivation) {
            if (
                !Configure::read(
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
            $User->getCategories()->getCustom('read')
        );
        switch ($User->getCategories()->getType()) {
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
            $User->getCategories()->getAll('read', 'select')
        );
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
            if (
// … directly on entries/view but not inline
                ($this->request->getParam('action') === 'view' && !$this->request->is('ajax'))
                // … directly in entries/mix
                || $this->request->getParam('action') === 'mix'
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
     * @param \Saito\Posting\Basic\BasicPostingInterface $posting posting for root entry
     * @return void
     */
    protected function _setRootEntry(BasicPostingInterface $posting)
    {
        if (!$posting->isRoot()) {
            $root = $this->Entries->find()
                ->select(['user_id', 'Users.user_type'])
                ->where(['Entries.id' => $posting->get('tid')])
                ->contain(['Users'])
                ->first();
        } else {
            $root = $posting;
        }
        $this->set('rootEntry', $root);
    }
}
