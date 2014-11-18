<?php

	namespace App\View\Cell;

	use App\Lib\View\Cell\AppStatisticTrait;
	use Saito\View\Cell\SlidetabCell;

	class SlidetabUserlistCell extends SlidetabCell {

		use AppStatisticTrait;

		protected $_validCellOptions = [];

		public function display() {
			$this->set('online', static::getUserOnline());
			$this->set('registered', static::getNUserOnline());
		}

		protected function _getSlidetabId() {
			return 'userlist';
		}

	}
