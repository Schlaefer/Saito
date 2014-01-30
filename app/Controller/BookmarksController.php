<?php

	App::uses('AppController', 'Controller');

/**
 * Bookmarks Controller
 *
 * @property Bookmark $Bookmark
 */
class BookmarksController extends AppController {

	public $helpers = array(
		'EntryH',
	);

/**
 * @throws MethodNotAllowedException
 */
	public function index() {
		if (!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException;
		}
		$bookmarks = $this->Bookmark->find('all', array(
				'contain' => array(
						'Entry' => array(
								'Category', 'User'
							)
						),
				'conditions' => array(
						'Bookmark.user_id' => $this->CurrentUser->getId(),
				),
				'order' => 'Bookmark.id DESC',
		));
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
		if ($this->request->is('post')) {
			$data = array(
					'user_id' => $this->CurrentUser->getId(),
					'entry_id' => $this->request->data['id'],
			);
			$this->Bookmark->create();
			if ($this->Bookmark->save($data)) {
				return true;
			} else {
				return false;
			}
		}
	}

/**
 * @param null $id
 * @throws MethodNotAllowedException
 */
	public function edit($id = null) {
		if (!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException;
		}
		$bookmark = $this->_getBookmark($id, $this->CurrentUser->getId());
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Bookmark->save($this->request->data)) {
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The bookmark could not be saved. Please, try again.'));
			}
		} else {
			$this->initBbcode();
			$this->request->data = $bookmark;
		}
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
		if ($this->Bookmark->delete()) {
			return true;
		}
		return false;
	}

/**
 * Returns unsanitized bookmark
 *
 * @param $bookmarkId
 * @param $userId
 * @return mixed
 * @throws NotFoundException if bookmark does not exist
 * @throws MethodNotAllowedException if bookmark does not belong to current user
 */
	protected function _getBookmark($bookmarkId, $userId) {
		$this->Bookmark->id = $bookmarkId;
		if (!$this->Bookmark->exists()) {
			throw new NotFoundException(__('Invalid bookmark'));
		}
		$this->Bookmark->contain(array(
					'Entry' => array(
							'Category', 'User'
					))
			);
		$this->Bookmark->sanitize(false);
		$bookmark = $this->Bookmark->findById($bookmarkId);
		if ($userId != $bookmark['Bookmark']['user_id']) {
			throw new MethodNotAllowedException;
		}
		return $bookmark;
	}

}