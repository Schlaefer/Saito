<?php

	App::uses('SaitoHelpAppController', 'SaitoHelp.Controller');

	class SaitoHelpsController extends SaitoHelpAppController {

		/**
		 * redirects help/<id> to help/<current language>/id
		 *
		 * @param $id help page ID
		 */
		public function languageRedirect($id) {
			$this->autoRender = false;
			$_language = Configure::read('Config.language');
			$this->redirect("/help/$_language/$id");
			return;
		}

		public function view($lang, $id) {
			$help = $this->SaitoHelp->find('first',
					[
							'conditions' => [
									'id' => $id,
									'language' => $lang
							]
					]);
			// try fallback to english default language
			if (!$help && $lang !== 'eng') {
				$this->redirect("/help/eng/$id");
			}
			if ($help) {
				$this->set('text', $help['SaitoHelp']['text']);
			} else {
				$this->Session->setFlash(__('sh.nf'), 'flash/error');
				$this->redirect('/');
				return;
			}
			$this->set('title_for_page', __('Help'));
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow();
		}

	}
