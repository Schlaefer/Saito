<?php
App::uses('AppController', 'Controller');
/**
 * Bookmarks Controller
 *
 * @property Bookmark $Bookmark
 */
class BookmarksController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		if (!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException;
		}
		$bookmarks = $this->Bookmark->find('all', array(
				'contain' => array('Entry'),
				'conditions' => array(
						'Bookmark.user_id' => $this->CurrentUser->getId(),
				),
				'order' => 'Bookmark.created DESC',
		));
		$this->set('bookmarks', $bookmarks);
	}

/**
 * add method
 *
 * @return void
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
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->CurrentUser->isLoggedIn()) {
			throw new MethodNotAllowedException;
		}

		$bookmark = $this->_getBookmark($id, $this->CurrentUser->getId());

		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Bookmark->save($this->request->data)) {
//				$this->Session->setFlash(__('The bookmark has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The bookmark could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $bookmark;
		}
	}

/**
 * delete method
 *
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$bookmark = $this->_getBookmark($id, $this->CurrentUser->getId());
		$this->Bookmark->id = $id;
		if ($this->Bookmark->delete()) {
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Bookmark was not deleted'));
		return $this->redirect(array('action' => 'index'));
	}

	/**
	 * Checks if bookmark exists and belongs to a user
	 *
	 * @param int $bookmark_id
	 * @param int $user_id
	 * @return array Bookmark
	 * @throws NotFoundException
	 * @throws MethodNotAllowedException
	 */
	protected function _getBookmark($bookmark_id, $user_id) {
		$this->Bookmark->id = $bookmark_id;
		if (!$this->Bookmark->exists()) {
			throw new NotFoundException(__('Invalid bookmark'));
		}

		$bookmark = $this->Bookmark->read(null, $bookmark_id);
		if ($user_id != $bookmark['Bookmark']['user_id']) {
			throw new MethodNotAllowedException;
		}
		return $bookmark;
	}

}
