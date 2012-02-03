<?php

class CategoriesController extends AppController {

	var $name = 'Categories';

	/*
	  function index() {
	  $this->Category->recursive = 0;
	  $this->set('categories', $this->paginate());
	  }

	  function view($id = null) {
	  if (!$id) {
	  $this->Session->setFlash(__('Invalid category', true));
	  $this->redirect(array('action' => 'index'));
	  }
	  $this->set('category', $this->Category->read(null, $id));
	  }

	  function add() {
	  if (!empty($this->data)) {
	  $this->Category->create();
	  if ($this->Category->save($this->data)) {
	  $this->Session->setFlash(__('The category has been saved', true));
	  $this->redirect(array('action' => 'index'));
	  } else {
	  $this->Session->setFlash(__('The category could not be saved. Please, try again.', true));
	  }
	  }
	  }

	  function edit($id = null) {
	  if (!$id && empty($this->data)) {
	  $this->Session->setFlash(__('Invalid category', true));
	  $this->redirect(array('action' => 'index'));
	  }
	  if (!empty($this->data)) {
	  if ($this->Category->save($this->data)) {
	  $this->Session->setFlash(__('The category has been saved', true));
	  $this->redirect(array('action' => 'index'));
	  } else {
	  $this->Session->setFlash(__('The category could not be saved. Please, try again.', true));
	  }
	  }
	  if (empty($this->data)) {
	  $this->data = $this->Category->read(null, $id);
	  }
	  }

	  function delete($id = null) {
	  if (!$id) {
	  $this->Session->setFlash(__('Invalid id for category', true));
	  $this->redirect(array('action'=>'index'));
	  }
	  if ($this->Category->delete($id)) {
	  $this->Session->setFlash(__('Category deleted', true));
	  $this->redirect(array('action'=>'index'));
	  }
	  $this->Session->setFlash(__('Category was not deleted', true));
	  $this->redirect(array('action' => 'index'));
	  }
	 */

	function admin_index() {
		$this->Category->recursive = 0;
		$this->set('categories', $this->paginate());

	}

	function admin_view($id = null) {
		if ( !$id ) {
			$this->Session->setFlash(__('Invalid category', true));
			$this->redirect(array( 'action' => 'index' ));
		}
		$this->set('category', $this->Category->read(null, $id));

	}

	function admin_add() {
		if ( !empty($this->data) ) {
			$this->Category->create();
			if ( $this->Category->save($this->data) ) {
				$this->Session->setFlash(__('The category has been saved', true));
				$this->redirect(array( 'action' => 'index' ));
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.',
								true));
			}
		}
	}

	function admin_edit($id = null) {
		if ( !$id && empty($this->data) ) {
			$this->Session->setFlash(__('Invalid category', true));
			$this->redirect(array( 'action' => 'index' ));
		}
		if ( !empty($this->data) ) {
			if ( $this->Category->save($this->data) ) {
				$this->Session->setFlash(__('The category has been saved', true));
				$this->redirect(array( 'action' => 'index' ));
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.',
								true));
			}
		}
		if ( empty($this->data) ) {
			$this->data = $this->Category->read(null, $id);
		}

	}

	function admin_delete($id = null) {
		if ( !$id ) {
			$this->Session->setFlash(__('Invalid id for category', true));
			$this->redirect(array( 'action' => 'index' ));
		}
		if ( $this->Category->delete($id) ) {
			$this->Session->setFlash(__('Category deleted', true));
			$this->redirect(array( 'action' => 'index' ));
		}
		$this->Session->setFlash(__('Category was not deleted', true));
		$this->redirect(array( 'action' => 'index' ));

	}

}

?>