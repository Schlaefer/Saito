<?php

	App::uses('AppController', 'Controller');

class SmiliesController extends AppController {

	public $name = 'Smilies';

	public $paginate = array(
		/*
		 * sets limit unrealisticly high so we should never reach the upper limit
		 * i.e. always show all entries on one page
		 */
		'limit' => 1000
	);

	public function admin_index() {
		$this->Smiley->recursive = 0;
		$this->set('smilies', $this->paginate());
	}

	public function admin_add() {
		if (!empty($this->request->data)) {
			$this->Smiley->create();
			if ($this->Smiley->save($this->request->data)) {
				$this->Session->setFlash(__('The smily has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The smily could not be saved. Please, try again.'));
			}
		}
	}

	public function admin_edit($id = null) {
		if (!$id && empty($this->request->data)) {
			$this->Session->setFlash(__('Invalid smily'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->request->data)) {
			if ($this->Smiley->save($this->request->data)) {
				$this->Session->setFlash(__('The smily has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The smily could not be saved. Please, try again.'));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $this->Smiley->read(null, $id);
		}
	}

	public function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for smily'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Smiley->delete($id)) {
			$this->Session->setFlash(__('Smily deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Smily was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
