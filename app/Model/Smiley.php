<?php

	App::uses('AppSettingModel', 'Lib/Model');

	class Smiley extends AppSettingModel {

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

		protected $_smilies;

		public function load($force = false) {
			if ($force) {
				$this->_smilies = null;
				$this->clearCache();
			}

			if ($this->_smilies !== null) {
				return $this->_smilies;
			}

			Stopwatch::start('Smiley::load');
			$this->_smilies = Cache::read('Saito.Smilies.data');
			if (!$this->_smilies) {
				$this->_smilies = [];
				$smiliesRaw = $this->find('all', ['order' => 'Smiley.order ASC']);

				foreach ($smiliesRaw as $smileyRaw) {
					// 'image' defaults to 'icon'
					if (empty($smileyRaw['Smiley']['image'])) {
						$smileyRaw['Smiley']['image'] = $smileyRaw['Smiley']['icon'];
					}
					// @bogus: if title is unknown it should be a problem
					if ($smileyRaw['Smiley']['title'] === null) {
						$smileyRaw['Smiley']['title'] = '';
					}
					// set type
					$smileyRaw['Smiley']['type'] = $this->_getType($smileyRaw['Smiley']);

					// adds smiley-data to every smiley-code
					foreach ($smileyRaw['SmileyCode'] as $smileyRawCode) {
						unset($smileyRaw['Smiley']['id']);
						$smileyRaw['Smiley']['code'] = $smileyRawCode['code'];
						$this->_smilies[] = $smileyRaw['Smiley'];
					}
				}

				Cache::write('Saito.Smilies.data', $this->_smilies);
			};

			Stopwatch::stop('Smiley::load');
			return $this->_smilies;
		}

		/**
		 * detects smiley type
		 *
		 * @param array $smiley
		 * @return string image|font
		 */
		protected function _getType($smiley) {
			if (preg_match('/^.*\.[\w]{3,4}$/i', $smiley['image'])) {
				return 'image';
			} else {
				return 'font';
			}
		}

	}
