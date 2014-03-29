<?php

	App::uses('AppController', 'Controller');

/**
 * Esnotifications Controller
 *
 * @property Esnotification $Esnotification
 */
	class EsnotificationsController extends AppController {

/**
 * delete method
 *
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
		public function unsubscribe($id = null) {
			$this->Esnotification->id = $id;
			if (!$this->Esnotification->exists()) {
				throw new NotFoundException(__('Invalid esnotification'));
			}

			$deactivate = $this->Esnotification->read('deactivate');
			if (!isset($this->request->params['named']['token'])
					|| (int)$this->request->params['named']['token'] !== (int)$deactivate['Esnotification']['deactivate']
			) {
				throw new MethodNotAllowedException();
			}
			if ($this->Esnotification->deleteNotificationWithId($id)) {
				$this->Session->setFlash(__('Succesfully unsubscribed.'), 'flash/success');
				$this->redirect('/');
			}
			$this->Session->setFlash(__('Error. Could not unsubscribe.'));
			$this->redirect('/');
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow('unsubscribe');
		}

	}

