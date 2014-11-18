<?php

	namespace Saito\Test\Model\Table;

	use App\Model\Table\EntriesTable;

	class EntriesTableMock extends EntriesTable {

		public $_CurrentUser;

		public $_editPeriod;

		protected $_table = 'entries';

		public function initialize(array $config) {
			$this->entityClass('Entry');
			parent::initialize($config);
		}

		public function prepareMarkup($string) {
			return $string;
		}

		public function setting() {
			return $this->_setting('subject_maxlength');
		}

	}

