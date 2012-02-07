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

		public function admin_index() {
			$this->Category->recursive = 0;
			$this->set('categories', $this->paginate());
		}

		public function admin_add() {
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

		public function admin_edit($id = null) {
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
				$this->Category->contain();
				$this->data = $this->Category->read(null, $id);
			}
		}

		public function admin_delete($id = null) {
			if ( !$id ) {
				$this->Session->setFlash(__('Invalid id for category', true), 'flash/error');
				$this->redirect($this->referer(array( 'action' => 'index' )));
				exit();
			}

			/* check if category to exists */
			$this->Category->contain();
			$categoryToDelete = $this->Category->findById($id);
			if ( empty($categoryToDelete) ) :
				$this->Session->setFlash(__('Category not found.', true), 'flash/error');
				$this->redirect($this->referer(array( 'action' => 'index' )));
				exit();
			endif;

			if ( isset($this->data['Category']['modeDelete']) ):
				/* move category items before deleting the cateogry */
				if ( isset($this->data['Category']['modeMove']) && isset($this->data['Category']['targetCategory']) ):
					$targetId = (int)$this->data['Category']['targetCategory'];

					/* check that target category != category to delete */
					if ( (int)$targetId === (int)$id ) :
						$this->Session->setFlash("Really? I mean … really?!", 'flash/error');
						$this->redirect($this->referer(array( 'action' => 'index' )));
						exit();
					endif;

					/* make sure that target category exists */
					$this->Category->contain();
					$categoryToDelete = $this->Category->findById($targetId);
					if ( empty($categoryToDelete) ) :
						$this->Session->setFlash(__('Target category not found.', true),
								'flash/error');
						$this->redirect($this->referer());
						exit();
					endif;

					/*
					 * @td move category items
					 * @td notice user
					 */

				endif; // move entries

				/*
				* @td delete category
				* @td notice user
				* @td redirect
				*/
				/*
					if ( $this->Category->delete($id) ) {
					$this->Session->setFlash(__('Category deleted', true));
					$this->redirect(array( 'action' => 'index' ));
					}
				*/

			endif; // delete category

			/* get categories for target <select> */
			$categories = $this->Category->getCategoriesSelectForAccession(0);
			unset($categories[$id]);
			$this->set('targetCategory', $categories);

			$this->Category->contain();
			$this->data = $this->Category->read(null, $id);
		}

	}

?>