<?php
class SmiliesController extends AppController {

	public $name = 'Smilies';

	public function admin_index() {
		$this->Smiley->recursive = 0;
		$this->set('smilies', $this->paginate());
	}

	public function admin_add() {
		if (!empty($this->data)) {
			$this->Smiley->create();
			if ($this->Smiley->save($this->data)) {
				$this->Session->setFlash(__('The smily has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The smily could not be saved. Please, try again.', true));
			}
		}
	}

	public function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid smily', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Smiley->save($this->data)) {
				$this->Session->setFlash(__('The smily has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The smily could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Smiley->read(null, $id);
		}
	}

	public function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for smily', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Smiley->delete($id)) {
			$this->Session->setFlash(__('Smily deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Smily was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
?>