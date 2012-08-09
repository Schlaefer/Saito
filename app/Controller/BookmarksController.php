<?php
App::uses('AppController', 'Controller');
/**
 * Bookmarks Controller
 *
 * @property Bookmark $Bookmark
 */
class BookmarksController extends AppController {

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

	public function delete($id = null) {
		if (!$this->request->is('ajax')) {
			throw new BadRequestException;
		}
		$id = $this->request->data['id'];
		$bookmark = $this->_getBookmark($id, $this->CurrentUser->getId());
		$this->autoRender = false;
		$this->Bookmark->id = $id;
		if ($this->Bookmark->delete()) {
			return true;
		}
		return false;
	}

	protected function _getBookmark($bookmark_id, $user_id) {
		$this->Bookmark->id = $bookmark_id;
		if (!$this->Bookmark->exists()) {
			throw new NotFoundException(__('Invalid bookmark'));
		}
		$this->Bookmark->contain(array(
					'Entry' => array(
							'Category', 'User'
					))
			);
		$bookmark = $this->Bookmark->findById($bookmark_id);
		if ($user_id != $bookmark['Bookmark']['user_id']) {
			throw new MethodNotAllowedException;
		}
		return $bookmark;
	}

}