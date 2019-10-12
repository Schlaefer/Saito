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

use App\Controller\Component\AutoReloadComponent;
use App\Controller\Component\MarkAsReadComponent;
use App\Controller\Component\RefererComponent;
use App\Controller\Component\ThreadsComponent;
use App\Model\Table\EntriesTable;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Routing\RequestActionTrait;
use Saito\Exception\SaitoForbiddenException;
use Saito\Posting\Posting;
use Saito\Posting\PostingInterface;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Class EntriesController
 *
 * @property CurrentUserInterface $CurrentUser
 * @property EntriesTable $Entries
 * @property MarkAsReadComponent $MarkAsRead
 * @property RefererComponent $Referer
 * @property ThreadsComponent $Threads
 */
class EntriesController extends AppController
{
    use RequestActionTrait;

    public $helpers = ['Posting', 'Text'];

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

        $this->loadComponent('MarkAsRead');
        $this->loadComponent('Referer');
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

        /** @var AutoReloadComponent */
        $autoReload = $this->loadComponent('AutoReload');
        $autoReload->after($this->CurrentUser);

        Stopwatch::stop('Entries->index()');
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
        $tid = (int)$tid;
        if ($tid <= 0) {
            throw new BadRequestException();
        }

        $postings = $this->Entries->treeForNode(
            $tid,
            ['root' => true, 'complete' => true]
        );

        /// redirect sub-posting to mix view of thread
        if (!$postings) {
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
        if (!$this->CurrentUser->getCategories()->permission('read', $root->get('category'))) {
            return $this->_requireAuth();
        }

        $this->_setRootEntry($root);
        $this->Title->setFromPosting($root, __('view.type.mix'));

        $this->set('showBottomNavigation', true);
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

        if (!$this->CurrentUser->getCategories()->permission('read', $entry->get('category'))) {
            return $this->_requireAuth();
        }

        $this->set('entry', $entry);
        $this->Threads->incrementViews($entry);
        $this->_setRootEntry($entry);
        $this->_showAnsweringPanel();

        $this->MarkAsRead->posting($entry);

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
     * @return void|\Cake\Network\Response
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
     * @return void|\Cake\Network\Response
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function edit($id = null)
    {
        if (empty($id)) {
            throw new BadRequestException;
        }

        /** @var PostingInterface */
        $posting = $this->Entries->get($id);
        if (!$posting) {
            throw new NotFoundException;
        }

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
     * @return void|\Cake\Network\Response
     */
    public function threadLine($id = null)
    {
        $posting = $this->Entries->get($id);
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
            $posting = $this->Entries->get($id, ['return' => 'Entity']);

            if (empty($posting)) {
                throw new \InvalidArgumentException('Posting to mark solved not found.');
            }

            if ($posting->isRoot()) {
                throw new \InvalidArgumentException('Root postings cannot mark themself solved.');
            }

            $rootId = $posting->get('tid');
            $rootPosting = $this->Entries->get($rootId);
            if ($rootPosting->get('user_id') !== $this->CurrentUser->getId()) {
                throw new SaitoForbiddenException(
                    sprintf('Attempt to mark posting %s as solution.', $posting->get('id')),
                    ['CurrentUser' => $this->CurrentUser]
                );
            }

            $success = $this->Entries->toggleSolve($posting);

            if (!$success) {
                throw new BadRequestException;
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

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody(json_encode($out));

        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        Stopwatch::start('Entries->beforeFilter()');

        $this->Security->setConfig(
            'unlockedActions',
            ['solve', 'view']
        );
        $this->Authentication->allowUnauthenticated(['index', 'view', 'mix', 'update']);

        Stopwatch::stop('Entries->beforeFilter()');
    }

    /**
     * set view vars for category chooser
     *
     * @param CurrentUserInterface $User CurrentUser
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
            if (// … directly on entries/view but not inline
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
