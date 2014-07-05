<?php

	App::uses('AppHelper', 'View/Helper');
	App::uses('MarkitupHelper', 'Markitup.View/Helper');

	class MarkitupEditorHelper extends MarkitupHelper {

		protected $_nextCssId;

		public function __construct(View $View, $settings = array()) {
			parent::__construct($View, $settings);
			$this->_nextCssId = Configure::read('Saito.markItUp.nextCssId');
		}

/**
 * Generates markItUp editor buttons based on forum config
 *
 * @param type $id
 * @return string
 */
		public function getButtonSet($id) {
			$css = '';
			$separator = ['separator' => '&nbsp;'];
			$bbcode = array(
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
				$separator,
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
				$separator
			);

			$this->_buildSmilies($bbcode, $css);
			$this->_buildAdditionalButtons($bbcode, $css);
			$markupSet = $this->_convertToJsMarkupSet($bbcode);
			$script = "markitupSettings = { id: '$id', markupSet: [$markupSet]};";
			$out = $this->Html->scriptBlock($script) .
					"<style type='text/css'>{$css}</style>";
			return $out;
		}

		protected function _convertToJsMarkupSet(array $bbcode) {
			$markitupSet = [];
			foreach ($bbcode as $set):
				$markitupSet[] = stripslashes(json_encode($set));
			endforeach;
			// markItUp callbacks: start with `function`, don't use `"`
			return preg_replace('/"(function.*?)"/i', '\\1', implode(",\n", $markitupSet));
		}

		protected function _buildAdditionalButtons(array &$bbcode, &$css) {
			$_additionalButtons = Configure::read(
				'Saito.markItUp.additionalButtons'
			);
			if (!empty($_additionalButtons)):
				foreach ($_additionalButtons as $name => $button):
					// 'Gacker' => ['name' => 'Gacker', 'replaceWith' => ':gacker:'],
					$bbcode[$name] = [
						'name' => $button['name'],
						'title' => $button['title'],
						'replaceWith' => $button['code'],
						'className' => 'btn-markItUp-' . $button['title']
					];
					if (isset($button['icon'])) {
						$css .= <<<EOF
.markItUp .markItUpButton{$this->_nextCssId} a {
		background-image: url({$this->request->webroot}theme/{$this->theme}/img/markitup/{$button['icon']});
		text-indent: -10000px;
		background-size: 100% 100%;
}
EOF;
					}
					$this->_nextCssId++;
				endforeach;
			endif;
		}

		protected function _buildSmilies(array &$bbcode, &$css) {
			$smilies = Configure::read('Saito.Smilies.smilies_all');
			$_smiliesPacked = [];

			$i = 1;
			foreach ($smilies as $smiley) {
				if (isset($_smiliesPacked[$smiley['icon']])) {
					continue;
				}
				$_smiliesPacked[$smiley['icon']] =
						array(
							'name' => '' /* $smiley['title'] */,
							// additional space to prevent smiley concatenation:
							// `:cry:` and `(-.-)zzZ` becomes `:cry:(-.-)zzZ` which outputs
							// smiley image for `:(`
							'replaceWith' => ' ' . $smiley['code'],
						);
				$css .= <<<EOF
.markItUp .markItUpButton{$this->_nextCssId}-{$i} a	{
		background-image: url({$this->request->webroot}theme/{$this->theme}/img/smilies/{$smiley['icon']});
}
EOF;

				$i++;
			}
			$this->_nextCssId++;

			$bbcode['Smilies'] = [
				'name' => "<i class='fa fa-s-smile-o'></i>",
				'title' => __('Smilies'),
				'className' => 'btn-markItUp-Smilies',
				'dropMenu' => $_smiliesPacked
			];
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
				$settings['parser'] = $this->Html->url(
					Router::url(array_merge($settings['parser'], array($settings['set'])))
				);
			}

			/*
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

