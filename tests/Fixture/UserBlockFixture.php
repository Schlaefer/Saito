<?php

	namespace App\Test\Fixture;

	use Cake\TestSuite\Fixture\TestFixture;

	class UserBlockFixture extends TestFixture {

		public $fields = [
			'id' => ['type' => 'integer', 'null' => false, 'default' => null,
				'unsigned' => true],
			'user_id' => ['type' => 'integer', 'null' => false, 'default' => null,
				'unsigned' => true],
			'reason' => ['type' => 'string', 'null' => true, 'default' => null,
				'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
			'by' => ['type' => 'integer', 'null' => true, 'default' => null,
				'unsigned' => true],
			'ends' => ['type' => 'datetime', 'null' => true, 'default' => null],
			'ended' => ['type' => 'datetime', 'null' => true, 'default' => null],
			'hash' => [
				'type' => 'string', 'null' => true, 'default' => null,
				'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'
			],
			'_constraints' => [
				'primary' => ['type' => 'primary', 'columns' => ['id']]
			],
			'_options' => [
				'charset' => 'utf8',
				'collate' => 'utf8_general_ci'
			]
		];

		public $records = [
			[
				'id' => 1,
				'user_id' => 1,
				'by' => 1,
				'ends' => '2014-08-11 08:59:43',
				'ended' => null
			],
			[
				'id' => 2,
				'user_id' => 2,
				'by' => 1,
				'ends' => null,
				'ended' => null
			],
		];

	}
