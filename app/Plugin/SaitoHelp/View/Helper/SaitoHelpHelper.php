<?php

	use Ciconia\Ciconia;
	use Ciconia\Extension\Gfm;

	App::import('Vendor', 'SaitoHelp.autoload');

	App::uses('AppHelper', 'View/Helper');

	class SaitoHelpHelper extends AppHelper {

		public $helpers = ['Html'];

		public function icon($id, array $options = []) {
			return $this->Html->link('<i class="fa fa-question-circle"></i>',
					"/help/$id",
					['escape' => false] + $options);
		}

		public function parse($text) {
			$ciconia = new Ciconia();

			$ciconia->addExtension(new Gfm\FencedCodeBlockExtension());
			$ciconia->addExtension(new Gfm\TaskListExtension());
			$ciconia->addExtension(new Gfm\InlineStyleExtension());
			$ciconia->addExtension(new Gfm\WhiteSpaceExtension());
			$ciconia->addExtension(new Gfm\TableExtension());
			$ciconia->addExtension(new Gfm\UrlAutoLinkExtension());

			return $ciconia->render($text);
		}

	}
