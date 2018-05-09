<?php

namespace Bookmarks\Controller;

use App\Controller\AppController;
use Bookmarks\Model\Table\BookmarksTable;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Saito\App\Registry;
use Saito\Exception\SaitoForbiddenException;

/**
 * Bookmarks Controller
 *
 * @property BookmarksTable $Bookmarks
 */
class BookmarksController extends AppController
{

    /**
     * Show all bookmarks.
     *
     * @throws MethodNotAllowedException
     * @return void
     */
    public function index()
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            throw new MethodNotAllowedException;
        }
        $this->loadModel('Bookmarks.Bookmarks');
        $categories = $this->CurrentUser->Categories->getAll('read');
        $bookmarks = $this->Bookmarks->find(
            'all',
            [
                'contain' => ['Entries' => ['Categories', 'Users']],
                'conditions' => [
                    'Bookmarks.user_id' => $this->CurrentUser->getId(),
                    'Entries.category_id IN' => $categories
                ],
                'order' => 'Bookmarks.id DESC',
            ]
        );
        $this->set('bookmarks', $bookmarks);
    }

    /**
     * Add a new bookmark.
     *
     * @return \Cake\Network\Response
     * @throws MethodNotAllowedException
     * @throws BadRequestException
     */
    public function add()
    {
        if (!$this->request->is('ajax') || !$this->request->is('post')) {
            throw new BadRequestException;
        }
        if (!$this->CurrentUser->isLoggedIn()) {
            throw new MethodNotAllowedException;
        }
        $this->autoRender = false;

        $data = [
            'user_id' => $this->CurrentUser->getId(),
            'entry_id' => $this->request->getData('id'),
        ];
        $bookmark = $this->Bookmarks->createBookmark($data);

        $body = !empty($bookmark) && !count($bookmark->getErrors());
        $this->response = $this->response->withStringBody($body);
        $this->response = $this->response->withType('json');

        return $this->response;
    }

    /**
     * Edit a bookmark.
     *
     * @param null $id bookmark-ID
     * @throws MethodNotAllowedException
     * @return void
     */
    public function edit($id = null)
    {
        $bookmark = $this->_getBookmark($id);

        if (!$this->request->is('post') && !$this->request->is('put')) {
            $posting = $bookmark->get('entry');
            $posting = Registry::newInstance(
                '\Saito\Posting\Posting',
                ['rawData' => $posting->toArray()]
            );
            $this->set(compact('bookmark', 'posting'));

            return;
        }

        $bookmark->set('id', $id);
        $this->Bookmarks->patchEntity(
            $bookmark,
            $this->request->getData(),
            ['fields' => ['comment']]
        );

        $success = $this->Bookmarks->save($bookmark);
        if (!$success) {
            $this->Flash->set(
                __('The bookmark could not be saved. Please, try again.')
            );

            return;
        }
        $this->redirect(
            ['action' => 'index', '#' => $bookmark->get('entry_id')]
        );
    }

    /**
     * Delete a single bookmark.
     *
     * @param null $bookmarkId bookmark-ID
     * @return \Cake\Network\Response
     * @throws BadRequestException
     */
    public function delete($bookmarkId = null)
    {
        if (!$this->request->is('ajax') || !$this->request->is('delete')) {
            throw new BadRequestException;
        }
        $this->autoRender = false;

        $bookmark = $this->_getBookmark($bookmarkId);
        $success = (bool)$this->Bookmarks->delete($bookmark);
        $this->response = $this->response->withStringBody($success);
        $this->response = $this->response->withType('json');

        return $this->response;
    }

    /**
     * {@inheritdoc}
     *
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Security->setConfig('unlockedActions', ['add']);
    }

    /**
     * Get a single bookmark
     *
     * @param int $bookmarkId bookmark-ID
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     * @throws SaitoForbiddenException
     * @return Entity
     */
    protected function _getBookmark($bookmarkId): Entity
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            throw new MethodNotAllowedException;
        }

        if (!$this->Bookmarks->exists($bookmarkId)) {
            throw new NotFoundException(__('Invalid bookmark.'));
        }

        $bookmark = $this->Bookmarks->find()
            ->contain(['Entries' => ['Categories', 'Users']])
            ->where(['Bookmarks.id' => $bookmarkId])
            ->first();

        if ($bookmark->get('user_id') !== $this->CurrentUser->getId()) {
            throw new SaitoForbiddenException(
                "Attempt to access bookmark $bookmarkId."
            );
        }

        return $bookmark;
    }
}
