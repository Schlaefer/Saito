<?php

	namespace Plugin\ExampleParser\Lib;

	class Editor extends \Saito\Markup\Editor {

		/**
		 * Short info shown in editor about used markup
		 *
		 * @return string
		 */
		public function getEditorHelp() {
			return '';
		}

		/**
		 * create MarkItUp buttonset
		 *
		 * @see http://markitup.jaysalvat.com/documentation/
		 * @return array
		 */
		public function getMarkupSet() {
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