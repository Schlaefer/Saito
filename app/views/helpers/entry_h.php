<?php

	App::import('Lib', 'SaitoEntry');

	# @td refactor helper name to 'EntryHelper'
	/**
	 * @package saito_entry
	 */

	class EntryHHelper extends SaitoEntry {

		public $helpers = array(
				'Form',
				'Html',
				'Session',
		);

		/**
		 * Generates markItUp editor buttons based on forum config
		 * 
		 * @param type $id
		 * @return string 
		 */
		public function generateMarkItUpEditorButtonSet($id) {
			$separator = array( 'separator' => '---------------' );

			/* build smilies for MarkItUp from the admin smilies settings */
			$smilies = Configure::read('Saito.Smilies.smilies_all');
			$smiliesMarkItUpPacked = array( );
			$smileyCss = '';
			$i = 1;
			foreach ( $smilies as $smiley ):
				if ( isset($smiliesMarkItUpPacked[$smiley['icon']]) )
					continue;
				// prepare  which is inserted into the markItUp config in the next stage
				$smiliesMarkItUpPacked[$smiley['icon']] = array( 'name' => $smiley['title'], 'replaceWith' => $smiley['code'] );
				// prepare CSS for each button so the smiley image is placed on it
				$smileyCss .= ".markItUp .markItUpButton12-{$i} a	{ background-image:url({$this->webroot}/theme/{$this->theme}/img/smilies/{$smiley['image']}); }";
				$i++;
			endforeach;
			$smileyCss = "<style type='text/css'>{$smileyCss}</style>";

			/* setup the BBCode for markitup as php array */
			$bbcodeSet = array(
					'Bold' => array( 'name' => 'Bold', 'key' => 'B', 'openWith' => '[b]', 'closeWith' => '[/b]' ),
					'Italic' => array( 'name' => 'Italic', 'key' => 'I', 'openWith' => '[i]', 'closeWith' => '[/i]' ),
					'Underline' => array( 'name' => 'Underline', 'key' => 'U', 'openWith' => '[u]', 'closeWith' => '[/u]' ),
					'Stroke' => array( 'name' => 'Stroke', 'openWith' => '[strike]', 'closeWith' => '[/strike]' ),
					'Code' => array( 'name' => 'Code', 'openWith' => '[code text]\n', 'closeWith' => '\n[/code]' ),
					$separator,
					'Bulleted list' => array( 'openWith' => '[list]\n[*] ', 'closeWith' => '\n[/list]' ),
					'List item' => array( 'openWith' => '[*] ' ),
					$separator,
					'Link' =>
					array(
							'name' => 'Link',
							'key' => 'L',
							'openWith' =>
							'[url=[![' . __('geshi_link_popup', true) . ']!]]',
							'closeWith' => '[/url]',
							'placeHolder' => __('geshi_link_placeholder', true),
					),
					'Picture' =>
					array(
							'name' => 'Bild',
							'key' => 'P',
							'replaceWith' => '[img][![' . __('geshi_picture_popup', true) . ']!][/img]'
					),
					'Upload' => array(
							'name' => '"Upload"',
							'closeWith' => "function(h) { return showUploadDialog('" . $this->Html->url(array( 'controller' => 'uploads', 'action' => 'index' )) . "'); }",
							'callback' => true ),
					'Media' => array(
							'name' => '"Media"',
							'beforeInsert' => <<<EOF
function() {  
	$('#markitup_media').dialog({
		show: {effect: "scale", duration: 200},
		hide: {effect: "fade", duration: 200},
		title: "Multimedia",
		close: function(event, ui) {
  		$('#markitup_media_message').hide();
		},
		});
		setTimeout("$('#markitup_media_txta').focus();", 210);
}
EOF
							,
							'callback' => TRUE
					),
					$separator,
					'Smilies' => array( 'name' => 'Smilies', 'dropMenu' => $smiliesMarkItUpPacked ),
					'Gacker' => array( 'name' => 'Gacker', 'replaceWith' => ':gacker:' ),
					'Popcorn' => array( 'name' => 'Popcorn', 'replaceWith' => ':popcorn:' ),
			);

			$markitupSet = array( );

			/* converting the BBCode PHP array into JS */
			foreach ( $bbcodeSet as $k => $set ):
				if ( isset($set['callback']) ):
					unset($set['callback']);
					$out = array( );
					foreach ( $set as $attribute => $value ):
						$out[] = "'$attribute': $value";
					endforeach;
					$markitupSet[] = '{' . implode(', ', $out) . "}";
				else:
					$markitupSet[] = stripslashes(json_encode($set));
				endif;
			endforeach;

			$out = 'markitupSettings = { "id":"' . $id . '", markupSet: [' . implode(",\n",
							$markitupSet) . ']};';
			$out = $this->Html->scriptBlock($out) . $smileyCss;

			return $out;
		}

		public function generateThreadParams($params) {

			extract($params);
//		debug($last_refresh);
//		debug($entry_time);

			$is_new_post = false;
			if ( $level == 0 ) {
				if ( isset($last_refresh)
						&& strtotime($last_refresh) < strtotime($entry_time)
				) {
					$span_post_type = 'threadnew';
					$is_new_post = true;
				} else {
					$span_post_type = 'thread';
				}
			} else {
				if ( isset($last_refresh)
						&& strtotime($last_refresh) < strtotime($entry_time)
				) {
					$span_post_type = 'replynew';
					$is_new_post = true;
				} else {
					$span_post_type = 'reply';
				}
			}

			### determine act_thread  start ###
			$act_thread = false;
			if ( $this->params['action'] == 'view' ) {
				if ( $level == 0 ) {
					if ( $entry_current == $entry_viewed ) {
						$span_post_type = 'actthread';
						$act_thread = true;
					}
				} else {
					if ( $entry_current == $entry_viewed ) {
						$span_post_type = 'actreply';
						$act_thread = true;
					}
				}
			}
			### determine act_thread end ###

			$out = array(
					'act_thread',
					'is_new_post',
					'span_post_type',
			);

			return compact($out);
		}

		/**
		 * This function may be called serveral hundred times on the front page.
		 * Don't make ist slow, benchmark!
		 *
		 * @param type $entry
		 * @param type $params
		 * @return string
		 */
		public function getFastLink($entry, $params = array( 'class' => '' )) {
//		Stopwatch::start('Helper->EntryH->getFastLink()');
			$out = "<a href='{$this->webroot}entries/view/{$entry['Entry']['id']}' class='{$params['class']}'>{$entry['Entry']['subject']}" . (empty($entry['Entry']['text']) ? ' n/t' : '') . "</a>";
//		Stopwatch::stop('Helper->EntryH->getFastLink()');
			return $out;
		}

		public function getSubject($entry) {
			return $entry['Entry']['subject'] . (empty($entry['Entry']['text']) ? ' n/t' : '');
		}

		public function getCategorySelectForEntry($categories, $entry) {
			if ( $entry['Entry']['pid'] == 0 ):
				$out = $this->Form->input(
						'category',
						array(
						'options' => array( $categories ),
						'empty' => '',
						'label' => __('cateogry', true) . ':',
						'tabindex' => 1,
						'error' => array(
								'notEmpty' => __('error_category_empty', true),
						),
						)
				);
			else :
				// Send category for easy access in entries/preview when anwsering.
				// If an entry is actually saved this value is not used but is looked up in DB.
				$out = $this->Form->hidden('category');
			endif;

			return $out;
		}

	}

?>