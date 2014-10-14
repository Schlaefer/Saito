<?php

	App::uses('JbbCodeTextVisitor', 'Lib/Bbcode/jBBCode/Visitors');

	/**
	 * Class JbbCodeSmileyVisitor replaces ASCII smilies with images
	 */
	class JbbCodeSmileyVisitor extends JbbCodeTextVisitor {

		const DEBUG_SMILIES_KEY = ':smilies-debug:';

		protected $_replacements;

		protected $_useCache;

		public function __construct(\Helper $Helper, array $_sOptions) {
			parent::__construct($Helper, $_sOptions);
			$this->_useCache = !Configure::read('debug');
		}

		protected function _processTextNode($string, $node) {
			$s = $this->_getReplacements();
			$string = str_replace($s['codes'], $s['replacements'], $string);
			$string = $this->_debug($string, $s);
			return $string;
		}

		/**
		 * outputs all available smilies :allSmilies:
		 *
		 * useful for debugging
		 */
		protected function _debug($string, $smilies) {
			if (strpos($string, self::DEBUG_SMILIES_KEY) === false) {
				return $string;
			}
			$out[] = '<table class="table table-simple">';
			$out[] = '<tr><th>Icon</th><th>Code</th><th>Image</th><th>Title</th></tr>';
			foreach ($smilies['replacements'] as $k => $smiley) {
				$out[] = '<tr>';
				$out[$smilies['images'][$k]] = "<td>{$smiley}</td><td>{$smilies['codes'][$k]}</td><td>{$smilies['images'][$k]}</td><td>{$smilies['titles'][$k]}</td>";
				$out[] = '</tr>';
			}
			$out[] = '</table>';
			return str_replace(self::DEBUG_SMILIES_KEY, implode('', $out), $string);
		}

		protected function _getReplacements() {
			if (!$this->_replacements && $this->_useCache) {
				$this->_replacements = Cache::read('Saito.Smilies.html');
			}

			if (!$this->_replacements) {
				$this->_replacements = ['codes' => [], 'replacements' => [], 'images' => '', 'titles' => []];
				$this->_addSmilies($this->_replacements);
				$this->_addAdditionalButtons($this->_replacements);

				if ($this->_useCache) {
					Cache::write('Saito.Smilies.html', $this->_replacements);
				}
			}

			return $this->_replacements;
		}

		protected function _addSmilies(&$s) {
			$smilies = $this->_sOptions['smiliesData'];
			foreach ($smilies as $smiley) {
				$title = __d('nondynamic', $smiley['title']);
				$s['titles'][] = $title;
				$s['codes'][] = $smiley['code'];
				$s['images'][] = $smiley['image'];

				//= vector font smileys
				if ($smiley['type'] === 'font') {
					$s['replacements'][] = $this->Html->tag(
						'i',
						'',
						[
							'class' => "saito-smiley-font saito-smiley-{$smiley['image']}",
							'title' => $title
						]
					);
				//= pixel image smileys
				} else {
					$s['replacements'][] = $this->Html->image(
						'smilies/' . $smiley['image'],
						[
							'alt' => $smiley['code'],
							'class' => "saito-smiley-image",
							'title' => $title
						]
					);
				}
			}
		}

		protected function _addAdditionalButtons(&$s) {
			$additionalButtons = Configure::read('Saito.markItUp.additionalButtons');
			if (empty($additionalButtons)) {
				return;
			}
			foreach ($additionalButtons as $additionalButton) {
				// $s['codes'][] = ':gacker:';
				$s['codes'][] = $additionalButton['code'];
				// $s['replacements'][] = $this->Html->image('smilies/gacker_large.png');
				if ($additionalButton['type'] === 'image') {
					$additionalButton['replacement'] = $this->Html->image(
						'markitup' . DS . $additionalButton['replacement']
					);
				}
				$s['replacements'][] = $additionalButton['replacement'];
			}
		}

	}
