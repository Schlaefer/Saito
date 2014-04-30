<?php

	use Ciconia\Ciconia;
	use Ciconia\Extension\Gfm;

	App::uses('AppHelper', 'View/Helper');

	class SaitoHelpHelper extends AppHelper {

		public $helpers = ['Html'];

		public function icon($id, array $options = []) {
			return $this->Html->link('<i class="fa fa-question-circle"></i>',
					"/help/$id",
					['escape' => false] + $options);
		}

		public function parse($text, $CurrentUser) {
			$this->_CurrentUser = $CurrentUser;
			$this->_webroot = $this->Html->url('/', true);

			$ciconia = new Ciconia();

			$ciconia->addExtension(new Gfm\FencedCodeBlockExtension());
			$ciconia->addExtension(new Gfm\TaskListExtension());
			$ciconia->addExtension(new Gfm\InlineStyleExtension());
			$ciconia->addExtension(new Gfm\WhiteSpaceExtension());
			$ciconia->addExtension(new Gfm\TableExtension());
			$ciconia->addExtension(new Gfm\UrlAutoLinkExtension());


			$text = preg_replace_callback('/\[(?P<text>.*?)\]\[(?P<url>.*?)\]/',
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
				$url = str_replace(':uid', $uid , $url);
			}

			if (strpos($url, 'webroot:') === 0) {
				$url = str_replace('webroot:', $this->_webroot , $url);
			}

			return "[$text]($url)";
		}

	}
