<?php

	App::uses('AppController', 'Controller');

/**
 * Bookmarks Controller
 *
 * @property Bookmark $Bookmark
 */
class BookmarksController extends AppController {

	public $helpers = ['EntryH'];

/**
 * @throws MethodNotAllowedException
 */
	public function index() {
		if (!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException;
		}
		$bookmarks = $this->Bookmark->find('all', [
			'contain' => ['Entry' => ['Category', 'User']],
			'conditions' => ['Bookmark.user_id' => $this->CurrentUser->getId()],
			'order' => 'Bookmark.id DESC',
		]);
		$this->set('bookmarks', $bookmarks);
	}

/**
 * @return bool
 * @throws MethodNotAllowedException
 * @throws BadRequestException
 */
	public function add() {
		if (!$this->request->is('ajax')) {
			throw new BadRequestException;
		}
		if (!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException;
		}
		$this->autoRender = false;
		if (!$this->request->is('post')) {
			return false;
		}

		$data = [
			'user_id' => $this->CurrentUser->getId(),
			'entry_id' => $this->request->data['id'],
		];
		$this->Bookmark->create();
		return (bool)$this->Bookmark->save($data);
	}

	/**
	 * @param null $id
	 * @throws NotFoundException
	 * @throws MethodNotAllowedException
	 * @throws Saito\ForbiddenException
	 */
	public function edit($id = null) {
		$bookmark = $this->_getBookmark($id);

		if (!$this->request->is('post') && !$this->request->is('put')) {
			$this->request->data = $bookmark;
			return;
		}

		$data['Bookmark'] = [
			'id' => $id,
			'comment' => $this->request->data['Bookmark']['comment']
		];
		$success = $this->Bookmark->save($data);
		if (!$success) {
			$this->Session->setFlash(
				__('The bookmark could not be saved. Please, try again.'));
			return;
		}
		$this->redirect(['action' => 'index',
			'#' => $bookmark['Bookmark']['entry_id']]);
	}

/**
 * @param null $id
 * @return bool
 * @throws BadRequestException
 */
	public function delete($id = null) {
		if (!$this->request->is('ajax')) {
			throw new BadRequestException;
		}

		$this->_getBookmark($id, $this->CurrentUser->getId());
		$this->autoRender = false;
		$this->Bookmark->id = $id;
		return (bool)$this->Bookmark->delete();
	}

	public function beforeFilter() {
		parent::beforeFilter();

		$this->Security->unlockedActions = ['add'];
	}

	/**
	 * @param $id
	 * @return mixed
	 * @throws NotFoundException
	 * @throws MethodNotAllowedException
	 * @throws Saito\ForbiddenException
	 */
	protected function _getBookmark($id) {
		if (!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException;
		}

		if (!$this->Bookmark->exists($id)) {
			throw new NotFoundException(__('Invalid bookmark.'));
		}

		$this->Bookmark->contain(['Entry' => ['Category', 'User']]);
		$bookmark = $this->Bookmark->findById($id);

		if ($bookmark['Bookmark']['user_id'] != $this->CurrentUser->getId()) {
			throw new Saito\ForbiddenException("Attempt to edit bookmark $id.");
		}
		return $bookmark;
	}

}