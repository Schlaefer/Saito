<?php

	namespace App\View\Cell;

	use App\Lib\View\Cell\AppStatisticTrait;
    use Saito\App\Registry;
    use Saito\View\Cell\SlidetabCell;

	class SlidetabUserlistCell extends SlidetabCell {

		protected $_validCellOptions = [];

		public function display() {
            /** @var \Saito\App\Stats $stats */
            $stats = Registry::get('AppStats');
			$this->set('online', $stats->getRegistredUsersOnline());
			$this->set('registered', $stats->getNumberOfRegisteredUsersOnline());
		}

		protected function _getSlidetabId() {
			return 'userlist';
		}

	}
