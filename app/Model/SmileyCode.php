<?php

	App::uses('AppSettingModel', 'Lib/Model');

	class SmileyCode extends AppSettingModel {

		public $name = 'SmileyCode';

		public $displayField = 'code';

		public $belongsTo = [
			'Smiley' => [
				'className' => 'Smiley',
				'foreignKey' => 'smiley_id'
      ]
    ];

	}
