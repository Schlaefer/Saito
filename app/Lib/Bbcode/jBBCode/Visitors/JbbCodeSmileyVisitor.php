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
			$replacements = $this->_getReplacements();
			$string = str_replace($replacements['codes'], $replacements['html'], $string);
			$string = $this->_debug($string, $replacements);
			return $string;
		}

		/**
		 * outputs all available smilies :allSmilies:
		 *
		 * useful for debugging
		 */
		protected function _debug($string, $replacements) {
			if (strpos($string, self::DEBUG_SMILIES_KEY) === false) {
				return $string;
			}
			$smilies = $this->_sOptions['smiliesData'];
			$out[] = '<table class="table table-simple">';
			$out[] = '<tr><th>Icon</th><th>Code</th><th>Image</th><th>Title</th></tr>';
			foreach ($replacements['html'] as $k => $smiley) {
				$title = $this->_l10n($smilies[$k]['title']);
				$out[] = '<tr>';
				$out[] = "<td>{$smiley}</td><td>{$smilies[$k]['code']}</td><td>{$smilies[$k]['image']}</td><td>{$title}</td>";
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
				$this->_replacements = ['codes' => [], 'html' => []];
				$this->_addSmilies($this->_replacements);
				$this->_addAdditionalButtons($this->_replacements);

				if ($this->_useCache) {
					Cache::write('Saito.Smilies.html', $this->_replacements);
				}
			}

			return $this->_replacements;
		}

		protected function _addSmilies(&$replacements) {
			$smilies = $this->_sOptions['smiliesData'];
			foreach ($smilies as $k => $smiley) {
				$replacements['codes'][] = $smiley['code'];
				$title = $this->_l10n($smiley['title']);

				//= vector font smileys
				if ($smiley['type'] === 'font') {
					$replacements['html'][$k] = $this->Html->tag(
						'i',
						'',
						[
							'class' => "saito-smiley-font saito-smiley-{$smiley['image']}",
							'title' => $title
						]
					);
				//= pixel image smileys
				} else {
					$replacements['html'][$k] = $this->Html->image(
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

		protected function _l10n($string) {
			return __d('nondynamic', $string);
		}

		protected function _addAdditionalButtons(&$replacements) {
			$additionalButtons = Configure::read('Saito.markItUp.additionalButtons');
			if (empty($additionalButtons)) {
				return;
			}
			foreach ($additionalButtons as $additionalButton) {
				// $s['codes'][] = ':gacker:';
				$replacements['codes'][] = $additionalButton['code'];
				// $s['html'][] = $this->Html->image('smilies/gacker_large.png');
				if ($additionalButton['type'] === 'image') {
					$additionalButton['replacement'] = $this->Html->image(
						'markitup' . DS . $additionalButton['replacement']
					);
				}
				$replacements['html'][] = $additionalButton['replacement'];
			}
		}

	}
