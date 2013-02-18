<?php

	App::uses('AppHelper', 'View/Helper');
	App::uses('MarkitupHelper', 'Markitup.View/Helper');

	class MarkitupEditorHelper extends MarkitupHelper {

		/**
		 * Generates markItUp editor buttons based on forum config
		 *
		 * @param type $id
		 * @return string
		 */
		public function getButtonSet($id) {
			$separator = array( 'separator' => '---------------' );

			/* build smilies for MarkItUp from the admin smilies settings */
			$markitupCssId = Configure::read('Saito.markItUp.nextCssId');
			$smilies = Configure::read('Saito.Smilies.smilies_all');
			$smiliesMarkItUpPacked = array( );
			$iconCss = '';
			$i = 1;
			foreach ( $smilies as $smiley ):
				if ( isset($smiliesMarkItUpPacked[$smiley['icon']]) )
					continue;
				// prepare JS  which is inserted into the markItUp config in the next stage
				$smiliesMarkItUpPacked[$smiley['icon']] =
						array(
								'name' => '' /* $smiley['title'] */,
								/*
								 * additional space to prevent [smiley1end][smiley2start] = [smiley3]
								 * see: https://github.com/Schlaefer/Saito/issues/32
								 */
								'replaceWith' => ' ' . $smiley['code'],
				);
				// prepare CSS for each button so the smiley image is placed on it
				$iconCss .= " .markItUp .markItUpButton{$markitupCssId}-{$i} a	{ background-image:url({$this->request->webroot}theme/{$this->theme}/img/smilies/{$smiley['icon']}); } ";
				$i++;
			endforeach;
			$markitupCssId++;

			/* setup the BBCode for markitup as php array */
			$bbcodeSet = array(
					'Bold' => array(
              'name' => "<i class='icon-bold'></i>", 'title' => __('Bold'),
              'key' => 'B', 'openWith' => '[b]', 'closeWith' => '[/b]'),
					'Italic' => array(
              'name' => "<i class='icon-italic'></i>", 'title' => __('Italic'),
              'key' => 'I', 'openWith' => '[i]', 'closeWith' => '[/i]' ),
					'Underline' => array(
              'name' => "<i class='icon-underline'></i>", 'title' => __('Underline'),
              'key' => 'U', 'openWith' => '[u]', 'closeWith' => '[/u]' ),
					'Stroke' => array(
              'name' => "<i class='icon-strikethrough'></i>", 'title' => __('Strike Through'),
              'openWith' => '[strike]', 'closeWith' => '[/strike]' ),
					'Code' => array(
              'name' => "<i class='icon-cogs'></i>", 'title' => __('Code'),
              'openWith' => '[code text]\n', 'closeWith' => '\n[/code]' ),
					'Bulleted list' => array(
              'name' => "<i class='icon-list-ul'></i>", 'title' => __('Bullet List'),
              'openWith' => '[list]\n[*] ', 'closeWith' => '\n[*]\n[/list]' ),
					$separator,
					'Link' => array(
              'name' => "<i class='icon-link'></i>",
              'title' => __('Link'),
              'key' => 'L',
              'openWith' =>
              '[url=[![' . __('geshi_link_popup') . ']!]]',
              'closeWith' => '[/url]',
              'placeHolder' => __('geshi_link_placeholder'),
            ),
					'Picture' => array(
              'name' => "<i class='icon-picture'></i>",
              'title' => __('Picture'),
              'key' => 'P',
              'replaceWith' => '[img][![' . __('geshi_picture_popup') . ']!][/img]'
            ),
					'Upload' => array(
							'name' => '"<i class=\'icon-upload-alt\'></i>"',
              'title' => '"' . __('Upload') .'"',
							'className' => '"btn-markItUp-Upload"',
							'callback' => true ),
					'Media' => array(
							'name' => '"<i class=\'icon-play-circle\'></i>"',
              'title' => '"' . __('Media') .'"',
							'beforeInsert' => <<<EOF
function() {
	$('#markitup_media').dialog({
		show: {effect: "scale", duration: 200},
		hide: {effect: "fade", duration: 200},
		title: "Multimedia",
		resizable: false,
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
			);

			$additionalButtons = Configure::read('Saito.markItUp.additionalButtons');
			if (!empty($additionalButtons)):
				foreach ( $additionalButtons as $additionalButtonName => $additionalButton):
					// 'Gacker' => array( 'name' => 'Gacker', 'replaceWith' => ':gacker:' ),
					$bbcodeSet[$additionalButtonName] = array(
							'name' => $additionalButton['title'],
							'title' => $additionalButton['title'],
							'replaceWith' => $additionalButton['code'],
						);
          if ( isset($additionalButton['icon']) ):
            $iconCss .= " .markItUp .markItUpButton{$markitupCssId} a	{ background-image: url({$this->request->webroot}theme/{$this->theme}/img/markitup/{$additionalButton['icon']}.png); } ";
            $iconCss .= " .markItUp .markItUpButton{$markitupCssId} a:hover	{ background-image: url({$this->request->webroot}theme/{$this->theme}/img/markitup/{$additionalButton['icon']}_hover.png); } ";
          endif;
					$markitupCssId++;
				endforeach;
			endif;

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

			$iconCss = "<style type='text/css'>{$iconCss}</style>";

			$out = 'markitupSettings = { "id":"' . $id . '", markupSet: [' . implode(",\n",
							$markitupSet) . ']};';
			$out = $this->Html->scriptBlock($out) . $iconCss;

			return $out;
		}

	protected function _build($settings) {
		$default = array(
			'set' => 'default',
			'skin' => 'simple',
			'settings' => 'mySettings',
			'parser' => array(
				'plugin' => 'markitup',
				'controller' => 'markitup',
				'action' => 'preview',
				'admin' => false,
			)
		);
		$settings = array_merge($default, $settings);

		if ($settings['parser']) {
			$settings['parser'] = $this->Html->url(Router::url(array_merge($settings['parser'], array($settings['set']))));
		}

    /**
     * Saito uses is owne css and sets
     */
    /*
		echo $this->Html->css(array(
			$this->paths['css'] . 'skins' . DS . $settings['skin'] . DS . 'style',
			$this->paths['css'] . 'sets' . DS . $settings['set'] . DS . 'style',
		), null, array('inline' => true));

		echo $this->Html->script($this->paths['js'] . 'sets' . DS . $settings['set'] . DS . 'set', true);
     *
     */

		return array('settings' => $settings, 'default' => $default);
	}

  }

?>