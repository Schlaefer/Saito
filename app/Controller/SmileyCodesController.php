<?php

	App::uses('AppController', 'Controller');

	class SmileyCodesController extends AppController {

		public $name = 'SmileyCodes';

		public $paginate = [
				// limit high enough so that no paging should occur
				'limit' => 1000
		];

		public function admin_index() {
			$this->SmileyCode->recursive = 0;
			$this->set('smileyCodes', $this->paginate());
		}

		public function admin_add() {
			if (!empty($this->request->data)) {
				$this->SmileyCode->create();
				if ($this->SmileyCode->save($this->request->data)) {
					$this->Session->setFlash(__('The smiley code has been saved'));
					$this->redirect(array('action' => 'index'));
				} else {
					$this->Session->setFlash(__('The smiley code could not be saved. Please, try again.'));
				}
			}
			$smilies = $this->SmileyCode->Smiley->find('list',
					array('fields' => 'Smiley.icon'));
			$this->set(compact('smilies'));
		}

		public function admin_edit($id = null) {
			if (!$id && empty($this->request->data)) {
				$this->Session->setFlash(__('Invalid smiley code'));
				$this->redirect(array('action' => 'index'));
			}
			if (!empty($this->request->data)) {
				if ($this->SmileyCode->save($this->request->data)) {
					$this->Session->setFlash(__('The smiley code has been saved'));
					$this->redirect(array('action' => 'index'));
				} else {
					$this->Session->setFlash(__('The smiley code could not be saved. Please, try again.'));
				}
			}
			if (empty($this->request->data)) {
				$this->request->data = $this->SmileyCode->read(null, $id);
			}
			$smilies = $this->SmileyCode->Smiley->find('list',
					array('fields' => 'Smiley.icon'));
			$this->set(compact('smilies'));
		}

		public function admin_delete($id = null) {
			if (!$id) {
				$this->Session->setFlash(__('Invalid id for smiley code'));
				$this->redirect(array('action' => 'index'));
			}
			if ($this->SmileyCode->delete($id)) {
				$this->Session->setFlash(__('Smiley code deleted'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Session->setFlash(__('Smiley code was not deleted'));
			$this->redirect(array('action' => 'index'));
		}

	}
