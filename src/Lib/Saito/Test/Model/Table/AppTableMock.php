<?php

	namespace Saito\Test\Model\Table;

	use App\Model\Table\EntriesTable;

	class AppTableMock extends EntriesTable {

		public function setAllowedInputFields($in) {
			$this->allowedInputFields = $in;
		}

	}
