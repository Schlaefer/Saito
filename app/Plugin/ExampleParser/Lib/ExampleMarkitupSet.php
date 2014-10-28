<?php

	App::uses('MarkitupSetInterface', 'Lib');

	class ExampleMarkitupSet implements MarkitupSetInterface {

		/**
		 * create MarkItUp buttonset
		 *
		 * @see http://markitup.jaysalvat.com/documentation/
		 * @return array
		 */
		public function getSet() {
			return [
				/*
				'Bold' => [
					'name' => "<i class='fa fa-bold'></i>",
					'title' => __('Bold'),
					'className' => 'btn-markItUp-Bold',
					'openWith' => '**',
					'closeWith' => '**'
				],
				*/
			];
		}

	}