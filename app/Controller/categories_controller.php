<?php

	class CategoriesController extends AppController {

		public $name = 'Categories';
		public $paginate = array(
				/*
				 * sets limit unrealisticly high so we should never reach the upper limit
				 * i.e. always show all entries on one page
				 */
				'limit' => 1000,
		);

		/*
		  function index() {
		  $this->Category->recursive = 0;
		  $this->set('categories', $this->paginate());
		  }

		  function view($id = null) {
		  if (!$id) {
		  $this->Session->setFlash(__('Invalid category'));
		  $this->redirect(array('action' => 'index'));
		  }
		  $this->set('category', $this->Category->read(null, $id));
		  }

		  function add() {
		  if (!empty($this->request->data)) {
		  $this->Category->create();
		  if ($this->Category->save($this->request->data)) {
		  $this->Session->setFlash(__('The category has been saved'));
		  $this->redirect(array('action' => 'index'));
		  } else {
		  $this->Session->setFlash(__('The category could not be saved. Please, try again.'));
		  }
		  }
		  }

		  function edit($id = null) {
		  if (!$id && empty($this->request->data)) {
		  $this->Session->setFlash(__('Invalid category'));
		  $this->redirect(array('action' => 'index'));
		  }
		  if (!empty($this->request->data)) {
		  if ($this->Category->save($this->request->data)) {
		  $this->Session->setFlash(__('The category has been saved'));
		  $this->redirect(array('action' => 'index'));
		  } else {
		  $this->Session->setFlash(__('The category could not be saved. Please, try again.'));
		  }
		  }
		  if (empty($this->request->data)) {
		  $this->request->data = $this->Category->read(null, $id);
		  }
		  }

		  function delete($id = null) {
		  if (!$id) {
		  $this->Session->setFlash(__('Invalid id for category'));
		  $this->redirect(array('action'=>'index'));
		  }
		  if ($this->Category->delete($id)) {
		  $this->Session->setFlash(__('Category deleted'));
		  $this->redirect(array('action'=>'index'));
		  }
		  $this->Session->setFlash(__('Category was not deleted'));
		  $this->redirect(array('action' => 'index'));
		  }
		 */

		public function admin_index() {
			$this->Category->recursive = 0;
			$this->set('categories', $this->paginate());
		}

		public function admin_add() {
			if ( !empty($this->request->data) ) {
				$this->Category->create();
				if ( $this->Category->save($this->request->data) ) {
					$this->Session->setFlash(__('The category has been saved'));
					$this->redirect(array( 'action' => 'index' ));
				} else {
					$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
				}
			}
		}

		public function admin_edit($id = null) {
			if ( !$id && empty($this->request->data) ) {
				$this->Session->setFlash(__('Invalid category'));
				$this->redirect(array( 'action' => 'index' ));
			}
			if ( !empty($this->request->data) ) {
				if ( $this->Category->save($this->request->data) ) {
					$this->Session->setFlash(__('The category has been saved'));
					$this->redirect(array( 'action' => 'index' ));
				} else {
					$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
				}
			}
			if ( empty($this->request->data) ) {
				$this->Category->contain();
				$this->request->data = $this->Category->read(null, $id);
			}
		}

		public function admin_delete($id = null) {
			if ( !$id ) {
				$this->Session->setFlash(__('Invalid id for category'), 'flash/error');
				$this->redirect($this->referer(array( 'action' => 'index' )));
				exit();
			}

			/* check if category to exists */
			$this->Category->contain();
			$categoryToDelete = $this->Category->findById($id);
			if ( empty($categoryToDelete) ) :
				$this->Session->setFlash(__('Category not found.'), 'flash/error');
				$this->redirect($this->referer(array( 'action' => 'index' )));
				exit();
			endif;

			if ( isset($this->request->data['Category']['modeDelete']) ):
				$failure = false;
			
				if ( isset($this->request->data['Category']['modeMove']) && isset($this->request->data['Category']['targetCategory']) ):
					/* move category items before deleting the cateogry */

					$targetId = (int)$this->request->data['Category']['targetCategory'];

					/* make sure that target category exists */
					$this->Category->contain();
					$categoryToDelete = $this->Category->findById($targetId);
					if ( empty($categoryToDelete) ) :
						$this->Session->setFlash(__('Target category not found.'),
								'flash/error');
						$this->redirect($this->referer());
						exit();
					endif;

					$this->Category->id = $id;
					if ( $this->Category->mergeIntoCategory($targetId) == false ) :
						$this->Session->setFlash(__('Error moving category.'), 'flash/error');
						$failure = $failure || true;
					else:
						$this->Session->setFlash(__('Category moved.'), 'flash/notice');
					endif;
				endif;

				$this->Category->id = $id;
				if ( $this->Category->deleteWithAllEntries() == false ) :
					$this->Session->setFlash(__("Error deleting category."), 'flash/error');
					$failure = $failure || true;
				else:
					$this->Session->setFlash(__('Category deleted.'), 'flash/notice');
				endif;

				if ( $failure == false ) :
					$this->redirect(array('action' => 'index', 'admin' => true));
					exit();
				endif;

			endif; // move or delete category

			/* get categories for target <select> */
			$categories = $this->Category->getCategoriesSelectForAccession(0);
			unset($categories[$id]);
			$this->set('targetCategory', $categories);

			$this->Category->contain();
			$this->request->data = $this->Category->read(null, $id);
		}

	}

?>