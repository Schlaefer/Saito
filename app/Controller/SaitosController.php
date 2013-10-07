<?php

	App::uses('AppController', 'Controller');

/**
 * Saitos Controller
 *
 */
	class SaitosController extends Controller {

		public $uses = array(
			'Shout'
		);

		public $components = [];

		public $autoRender = false;

/**
 * @return string
 * @throws BadRequestException
 */
		public function status() {
			if ($this->request->is('ajax') === false) {
				throw new BadRequestException();
			}
			$out = array(
				'lastShoutId' => $this->Shout->findLastId()
			);
			return json_encode($out);
		}

/**
 * Output current language strings as json
 */
		public function langJs() {
			// dummy translation to load po files
			__d('nondynamic', 'foo');
			__d('default', 'foo');
			$domains = I18n::domains();
			$translations = $domains['nondynamic'][Configure::read('Config.language')]['LC_MESSAGES'];
			$translations += $domains['default'][Configure::read('Config.language')]['LC_MESSAGES'];
			unset($translations['%po-header']);
			// $this->response->type('javascript');
			$this->response->cache('-1 minute', '+1 hour');
			$this->response->compress();
			return json_encode($translations);
		}

	}
