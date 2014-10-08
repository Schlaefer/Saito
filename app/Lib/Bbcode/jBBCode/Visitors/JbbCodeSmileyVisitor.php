<?php

	App::uses('JbbCodeTextVisitor', 'Lib/Bbcode/jBBCode/Visitors');

	/**
	 * Class JbbCodeSmileyVisitor replaces ASCII smilies with images
	 */
	class JbbCodeSmileyVisitor extends JbbCodeTextVisitor {

		protected function _processTextNode($string, $node) {
			$s = $this->_getSmilies();
			$string = str_replace($s['codes'], $s['replacements'], $string);
			return $string;
		}

		protected function _getSmilies($cache = true) {
			// @todo @bogus why Configure and not class variable?
			if ($s = Configure::read('Saito.Smilies.smilies_all_html')) {
				return $s;
			}

			if ($cache && $s = Cache::read('Saito.Smilies.smilies_all_html')) {
				Configure::write('Saito.Smilies.smilies_all_html', $s);
				return $s;
			}

			$s = ['codes' => [], 'replacements' => []];
			$this->_addSmilies($s);
			$this->_addAdditionalButtons($s);

			Configure::write('Saito.Smilies.smilies_all_html', $s);
			if ($cache) {
				Cache::write('Saito.Smilies.smilies_all_html', $s);
			}

			return $s;
		}

		protected function _addSmilies(&$s) {
			// @todo refactor: MVC|method?
			$smilies = Configure::read('Saito.Smilies.smilies_all');
			foreach ($smilies as $smiley) {
				$s['codes'][] = $smiley['code'];
				$s['replacements'][] = $this->Html->image(
					'smilies/' . $smiley['image'],
					['alt' => $smiley['code'], 'title' => $smiley['title']]
				);
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
