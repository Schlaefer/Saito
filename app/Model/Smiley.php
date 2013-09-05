<?php

	class Smiley extends AppModel {

		public $name = 'Smiley';
		public $validate = [
			'order' => [
				'numeric' => [
					'rule' => ['numeric'],
					//'message' => 'Your custom message here',
					//'allowEmpty' => false,
					//'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
				],
			],
		];
		public $hasMany = [
			'SmileyCode' => [
				'className'  => 'SmileyCode',
				'foreignKey' => 'smiley_id'
			]
		];

		public function load() {
			Stopwatch::start('Smiley::load');
			$smilies    = [];
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
					$smilies[]                   = $smileyRaw['Smiley'];
				}
			}
			Configure::write('Saito.Smilies.smilies_all', $smilies);
			Stopwatch::stop('Smiley::load');
		}

	}
