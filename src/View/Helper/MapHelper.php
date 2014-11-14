<?php

	App::uses('AppHelper', 'View/Helper');

	class MapHelper extends AppHelper {

		public $helpers = [
				'Html'
		];

		protected $_precision = 2;

		protected $_apiEnabled;

		protected $_apiKey;

		protected $_assetsIncluded = false;

		public function beforeRender($viewFile) {
			$this->_apiEnabled = (bool)Configure::read('Saito.Settings.map_enabled');
			if (!$this->_apiEnabled) {
				return;
			}
			$this->_apiKey = Configure::read('Saito.Settings.map_api_key');
		}

		protected function _includeAssets() {
			if (!$this->_apiEnabled || $this->_assetsIncluded === true) {
				return;
			}
			$this->Html->css([
							'../dist/leaflet/leaflet.css',
							'../dist/leaflet/MarkerCluster.Default.css'
					],
					['inline' => false]);
			$this->Html->script([
							'../dist/leaflet/leaflet.js',
							'../dist/leaflet/leaflet.markercluster.js',
							'//www.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=' . $this->_apiKey,
							'//www.mapquestapi.com/sdk/leaflet/v1.0/mq-geocoding.js?key=' . $this->_apiKey
					],
					['inline' => false, 'block' => 'script-head']);
			$this->_assetsIncluded = true;
		}

		public function map(array $users = [], array $options = []) {
			$this->_includeAssets();
			// generate options
			$defaults = [
					'fields' => [],
					'type' => 'world',
					'div' => [
							'class' => 'saito-usermap'
					]
			];
			foreach ($defaults as $key => $default) {
				if (isset($options[$key])) {
					if (is_array($options[$key])) {
						$options[$key] = am($default, $options[$key]);
					}
				} else {
					$options[$key] = $default;
				}
			}

			// show single user
			if (isset($users['User'])) {
				if ($options['type'] === 'world') {
					$options['type'] = 'single';
				}
				$users = [$users];
			}
			$edit = $options['type'] === 'edit';

			foreach ($users as $key => $user) {
				// @performance
				// filter out every field except the place fields
				$users[$key] = [
						'id' => (int)$user['User']['id'],
						'name' => $user['User']['username'],
						'lat' => (float)$user['User']['user_place_lat'],
						'lng' => (float)$user['User']['user_place_lng']
				];
				if ($edit) {
					$users[$key]['zoom'] = (int)$user['User']['user_place_zoom'];
				} else {
					// add simple jitter
					$rand = 0; //  rand(-9,9) / pow(10, $this->_precision + 1);
					$users[$key]['lat'] = round($users[$key]['lat'],
									$this->_precision) + $rand;
					$users[$key]['lng'] = round($users[$key]['lng'],
									$this->_precision) + $rand;
				}
			}

			$options['div'] += [
					'data-users' => json_encode($users),
					'data-params' => json_encode([
							'type' => $options['type'],
							'fields' => $options['fields']
					])
			];
			return $this->Html->div(null, '', $options['div']);
		}

	}
