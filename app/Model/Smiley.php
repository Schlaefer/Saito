<?php

	class Smiley extends AppModel {

		public $name = 'Smiley';

		public $validate = [
			'order' => [
				'numeric' => [
					'rule' => ['numeric']
				]
			]
		];

		public $hasMany = [
			'SmileyCode' => [
				'className' => 'SmileyCode',
				'foreignKey' => 'smiley_id'
			]
		];

		public function afterSave($created, $options = array()) {
			$this->clearCache();
		}

		public function clearCache() {
			Cache::delete('Saito.Smilies.smilies_all');
		}

		public function load($force = false) {
			Stopwatch::start('Smiley::load');

			if ($force) {
				$this->clearCache();
			} elseif (Configure::read('Saito.Smilies.smilies_all')) {
					return;
			}

			$smilies = Cache::read('Saito.Smilies.smilies_all');
			if (!$smilies) {
				$smilies = [];
				$smiliesRaw = $this->find('all', ['order' => 'Smiley.order ASC']);

				foreach ($smiliesRaw as $smileyRaw) {
					if (empty($smileyRaw['Smiley']['image'])) {
						$smileyRaw['Smiley']['image'] = $smileyRaw['Smiley']['icon'];
					}
					if ($smileyRaw['Smiley']['title'] === null) {
						$smileyRaw['Smiley']['title'] = '';
					}
					foreach ($smileyRaw['SmileyCode'] as $smileyRawCode) {
						unset($smileyRaw['Smiley']['id']);
						$smileyRaw['Smiley']['code'] = $smileyRawCode['code'];
						$smilies[] = $smileyRaw['Smiley'];
					}
				}
				Cache::write('Saito.Smilies.smilies_all', $smilies);
			};

			Configure::write('Saito.Smilies.smilies_all', $smilies);
			Stopwatch::stop('Smiley::load');
		}

	}
