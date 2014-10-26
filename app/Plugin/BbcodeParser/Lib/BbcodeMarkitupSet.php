<?php

	App::uses('MarkitupSetInterface', 'Lib');

	class BbcodeMarkitupSet implements MarkitupSetInterface {

		public function getSet() {
			return array(
				'Bold' => array(
					'name' => "<i class='fa fa-bold'></i>",
					'title' => __('Bold'),
					'className' => 'btn-markItUp-Bold',
					'key' => 'B',
					'openWith' => '[b]',
					'closeWith' => '[/b]'
				),
				'Italic' => array(
					'name' => "<i class='fa fa-italic'></i>",
					'title' => __('Italic'),
					'className' => 'btn-markItUp-Italic',
					'key' => 'I',
					'openWith' => '[i]',
					'closeWith' => '[/i]'
				),
				'Stroke' => array(
					'name' => "<i class='fa fa-strikethrough'></i>",
					'title' => __('Strike Through'),
					'className' => 'btn-markItUp-Stroke',
					'openWith' => '[strike]',
					'closeWith' => '[/strike]'
				),
				'Code' => array(
					'name' => "<i class='fa fa-s-code'></i>",
					'title' => __('Code'),
					'className' => 'btn-markItUp-Code',
					'openWith' => '[code=text]\n',
					'closeWith' => '\n[/code]'
				),
				'Bulleted list' => array(
					'name' => "<i class='fa fa-list-ul'></i>",
					'title' => __('Bullet List'),
					'className' => 'btn-markItUp-List',
					'openWith' => '[list]\n[*] ',
					'closeWith' => '\n[*]\n[/list]'
				),
				'Spoiler' => [
					'name' => "<i class='fa fa-stop'></i>",
					'className' => 'btn-markItUp-Spoiler',
					'title' => __('Spoiler'),
					'openWith' => '[spoiler]',
					'closeWith' => '[/spoiler]'
				],
				'separator',
				'Link' => array(
					'name' => "<i class='fa fa-link'></i>",
					'title' => __('Link'),
					'className' => 'btn-markItUp-Link',
					'key' => 'L',
					'openWith' =>
						'[url=[![' . __('geshi_link_popup') . ']!]]',
					'closeWith' => '[/url]',
					'placeHolder' => __('geshi_link_placeholder'),
				),
				'Media' => array(
					'name' => "<i class='fa fa-multimedia'></i>",
					'className' => 'btn-markItUp-Media',
					'title' => __('Media'),
					'key' => 'P',
				),
				'Upload' => array(
					'name' => '<i class=\'fa fa-upload\'></i>',
					'title' => __('Upload'),
					'className' => 'btn-markItUp-Upload'
				),
				'separator'
			);
		}

	}