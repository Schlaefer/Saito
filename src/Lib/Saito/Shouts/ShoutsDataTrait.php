<?php

	namespace Saito\Shouts;

	use App\Model\Table\ShoutsTable;
	use Cake\Core\Configure;
	use Cake\ORM\TableRegistry;

	trait ShoutsDataTrait {

		/**
		 * @var ShoutsTable
		 */
		protected $_ShoutModel = null;

		public function get() {
			return $this->_model()->get();
		}

		public function push($data) {
			return $this->_model()->push($data);
		}

		protected function _model() {
			if ($this->_ShoutModel !== null) {
				return $this->_ShoutModel;
			}
			$this->_ShoutModel = TableRegistry::get('Shouts');
			$this->_ShoutModel->maxNumberOfShouts = (int)Configure::read(
				'Saito.Settings.shoutbox_max_shouts'
			);
			return $this->_ShoutModel;
		}

	}