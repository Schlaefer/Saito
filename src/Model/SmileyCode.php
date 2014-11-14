<?php

	App::uses('AppSettingModel', 'Lib/Model');

	class SmileyCode extends AppSettingModel {

		public $name = 'SmileyCode';

		public $displayField = 'code';

		public $belongsTo = array(
			'Smiley' => array(
				'className' => 'Smiley',
				'foreignKey' => 'smiley_id'
			)
		);

		public function afterSave($created, $options = []) {
			parent::afterSave($created, $options);
		}

		public function afterDelete() {
			parent::afterDelete();
		}

	}