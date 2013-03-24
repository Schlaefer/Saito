<?php
App::uses('AppController', 'Controller');
/**
 * Shouts Controller
 *
 * @property Shout $Shout
 */
class ShoutsController extends AppController {

	public function index() {

		$this->autoLayout = false;

		if ($this->request->is('ajax')) {
			$this->_loadSmilies();
			$shouts = $this->Shout->find(
				'all',
				array(
					'order' => 'Shout.id DESC'
				)
			);

			$last_id = (int)$this->request->data['lastId'];
			$last_shout = current($shouts);
			if ($last_id === (int)$last_shout['Shout']['id']) {
				$this->autoRender = false;
				return;
			}

			$this->_initBbcodeHelper();
			$this->set('shouts', $shouts);
		}

	}

	public function add() {

		$this->autoRender = false;

		if ($this->request->is('ajax')) {
			$data = array(
				'Shout' => array(
					'text' => $this->request->data['text'],
					'user_id' => $this->CurrentUser->getId()
				)
			);
			return $this->Shout->push($data);
		}

	}

	public function beforeFilter() {
		parent::beforeFilter();

		if (Configure::read('Saito.Settings.shoutbox_enabled') == false) {
			throw new MethodNotAllowedException();
		}

		$this->Shout->maxNumberOfShouts = Configure::read('Saito.Settings.shoutbox_max_shouts');

	}

}
