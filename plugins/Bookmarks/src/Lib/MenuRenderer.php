<?php

	namespace Bookmarks\Lib;

	use Saito\Event\SaitoEventListener;

	class MenuRenderer implements SaitoEventListener {

		public function implementedSaitoEvents() {
			return [
				'Request.Saito.View.MainMenu.navItem' => 'onRender'
			];
		}

		public function onRender(array $eventData) {
			$View = $eventData['View'];
			$title = $View->Layout->textWithIcon(h(__('Bookmarks')), 'bookmark');
			// @todo i18n
			return ['title' => $title, 'url' => 'bookmarks'];
		}
	}