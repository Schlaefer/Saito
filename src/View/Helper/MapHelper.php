<?php

	namespace App\View\Helper;

	use App\Model\Entity\User;
	use Cake\Core\Configure;

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
					['block' => true]);
			$this->Html->script([
							'../dist/leaflet/leaflet.js',
							'../dist/leaflet/leaflet.markercluster.js',
							'//open.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=' . $this->_apiKey,
							'//open.mapquestapi.com/sdk/leaflet/v1.0/mq-geocoding.js?key=' . $this->_apiKey
					],
					['block' => 'script-head']);
			$this->_assetsIncluded = true;
		}

		public function map($users, array $options = []) {
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
						$options[$key] = array_merge($default, $options[$key]);
					}
				} else {
					$options[$key] = $default;
				}
			}

			// show single user
			if ($users instanceof User) {
				if ($options['type'] === 'world') {
					$options['type'] = 'single';
				}
				$users = [$users];
			}
			$edit = $options['type'] === 'edit';

			$usersForMap = [];
			foreach ($users as $key => $user) {
				// @performance
				// filter out every field except the place fields
				$usersForMap[$key] = [
					'id' => (int)$user->get('id'),
					'name' => $user->get('username'),
					'lat' => (float)$user->get('user_place_lat'),
					'lng' => (float)$user->get('user_place_lng')
				];
				if ($edit) {
					$usersForMap[$key]['zoom'] = (int)$user->get('user_place_zoom');
				} else {
					// add simple jitter
					$rand = 0; //  rand(-9,9) / pow(10, $this->_precision + 1);
					$usersForMap[$key]['lat'] = round($usersForMap[$key]['lat'],
									$this->_precision) + $rand;
					$usersForMap[$key]['lng'] = round($usersForMap[$key]['lng'],
									$this->_precision) + $rand;
				}
			}

			$options['div'] += [
					'data-users' => json_encode($usersForMap),
					'data-params' => json_encode([
							'type' => $options['type'],
							'fields' => $options['fields']
					])
			];
			return $this->Html->div(null, '', $options['div']);
		}

	}
