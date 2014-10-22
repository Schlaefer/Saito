<?php

	App::uses('SaitoEventListener', 'Lib/Saito/Event');

	class Userranks implements SaitoEventListener {

		/**
		 * @var array plugin-config
		 */
		protected $_settings;

		public function __construct($settings) {
			$this->_settings = $settings;
		}

		public function implementedSaitoEvents() {
			return [
				// event => local method to call on event
				'Request.Saito.View.User.beforeFullProfile' => 'onUserranks'
			];
		}

		public function onUserranks($eventData) {
			$user = $eventData['user'];

			foreach ($this->_settings['ranks'] as $treshold => $title) {
				$rank = $title;
				if ($user['User']['number_of_entries'] <= $treshold) {
					break;
				}
			}

			return [
				'title' => __d('userranks', 'rank'),
				'content' => $rank
			];
		}

	}