<?php
class SmileyCodesController extends AppController {

	public $name = 'SmileyCodes';

	public $paginate = array(
			/*
				* sets limit unrealisticly high so we should never reach the upper limit
				* i.e. always show all entries on one page
				*/
			'limit' => 1000,
	);

	function admin_index() {
		$this->SmileyCode->recursive = 0;
		$this->set('smileyCodes', $this->paginate());
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid smiley code', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('smileyCode', $this->SmileyCode->read(null, $id));
	}

	function admin_add() {
		if (!empty($this->data)) {
			$this->SmileyCode->create();
			if ($this->SmileyCode->save($this->data)) {
				$this->Session->setFlash(__('The smiley code has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The smiley code could not be saved. Please, try again.', true));
			}
		}
		$smilies = $this->SmileyCode->Smiley->find('list', array('fields' => 'Smiley.icon'));
		$this->set(compact('smilies'));
	}

	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid smiley code', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->SmileyCode->save($this->data)) {
				$this->Session->setFlash(__('The smiley code has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The smiley code could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->SmileyCode->read(null, $id);
		}
		$smilies = $this->SmileyCode->Smiley->find('list', array('fields' => 'Smiley.icon'));
		$this->set(compact('smilies'));
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for smiley code', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->SmileyCode->delete($id)) {
			$this->Session->setFlash(__('Smiley code deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Smiley code was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
?>