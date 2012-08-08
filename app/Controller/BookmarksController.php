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
			throw new ForbiddenException;
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
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Bookmark->id = $id;
		if (!$this->Bookmark->exists()) {
			throw new NotFoundException(__('Invalid bookmark'));
		}
		$this->set('bookmark', $this->Bookmark->read(null, $id));
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
			throw new ForbiddenException;
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
		$this->Bookmark->id = $id;
		if (!$this->Bookmark->exists()) {
			throw new NotFoundException(__('Invalid bookmark'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Bookmark->save($this->request->data)) {
				$this->Session->setFlash(__('The bookmark has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The bookmark could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Bookmark->read(null, $id);
		}
		$users = $this->Bookmark->User->find('list');
		$entries = $this->Bookmark->Entry->find('list');
		$this->set(compact('users', 'entries'));
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
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Bookmark->id = $id;
		if (!$this->Bookmark->exists()) {
			throw new NotFoundException(__('Invalid bookmark'));
		}
		if ($this->Bookmark->delete()) {
			$this->Session->setFlash(__('Bookmark deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Bookmark was not deleted'));
		$this->redirect(array('action' => 'index'));
	}

}
