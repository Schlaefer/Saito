<?php

	App::uses('AppController', 'Controller');

	class StatusController extends AppController {

		public $uses = [
			'Shout'
		];

		public $autoRender = false;

		/**
		 * Current app status ping
		 *
		 * @return string
		 * @throws BadRequestException
		 */
		public function status() {
			$data = [
				'lastShoutId' => $this->Shout->findLastId()
			];
			$data = json_encode($data);
			if ($this->request->accepts('text/event-streams')) {
				return $this->_statusAsEventStream($data);
			} else {
				return $this->_statusAsJson($data);
			}
		}

		protected function _statusAsEventStream($data) {
			// time in ms to next request
			$_retry = '10000';
			$this->response->type(['eventstream' => 'text/event-stream']);
			$this->response->type('eventstream');
			$this->response->disableCache();
			$_out = '';
			$_out .= "retry: $_retry\n";
			$_out .= 'data: ' . $data . "\n\n";
			return $_out;
		}

		protected function _statusAsJson($data) {
			if ($this->request->is('ajax') === false) {
				throw new BadRequestException();
			}
			return $data;
		}

		public function beforeFilter() {
			$this->Auth->allow(['status']);
		}

	}
