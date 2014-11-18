<?php

	namespace SaitoHelp\View\Helper;

	use Cake\View\Helper;
	use Ciconia\Ciconia;
	use Ciconia\Extension\Gfm;

	class SaitoHelpHelper extends Helper {

		public $helpers = ['Html', 'Layout'];

		public function icon($id, array $options = []) {
			$options += ['label' => '', 'target' => '_blank'];
			$options = ['class' => 'shp-icon', 'escape' => false] + $options;

			if ($options['label'] === true) {
				$options['label'] = __('Help');
			}
			if (!empty($options['label'])) {
				$options['label'] = h($options['label']);
			}

			$title = $this->Layout->textWithIcon($options['label'], 'question-circle');
			unset($options['label']);

			return $this->Html->link($title, "/help/$id", $options);
		}

		public function parse($text, $CurrentUser) {
			$this->_CurrentUser = $CurrentUser;
			$this->_webroot = $this->Html->url('/', true);

            // @todo 3.0 use https://github.com/fluxbb/commonmark already in composer
			$ciconia = new Ciconia();

			$ciconia->addExtension(new Gfm\FencedCodeBlockExtension());
			$ciconia->addExtension(new Gfm\TaskListExtension());
			$ciconia->addExtension(new Gfm\InlineStyleExtension());
			$ciconia->addExtension(new Gfm\WhiteSpaceExtension());
			$ciconia->addExtension(new Gfm\TableExtension());
			$ciconia->addExtension(new Gfm\UrlAutoLinkExtension());

			$text = preg_replace_callback('/\[(?P<text>.*?)\]\((?P<url>.*?)\)/',
				[$this, '_replaceUrl'], $text);

			return $ciconia->render($text);
		}

		protected function _replaceUrl($matches) {
			$text = $matches['text'];
			$url = $matches['url'];

			if (strpos($matches['url'], ':uid')) {
				if (!$this->_CurrentUser->isLoggedIn()) {
					return $text;
				}
				$uid = $this->_CurrentUser->getId();
				$url = str_replace(':uid', $uid, $url);
			}

			if (strpos($url, 'webroot:') === 0) {
				$url = str_replace('webroot:', $this->_webroot, $url);
			}

			return "[$text]($url)";
		}

	}
