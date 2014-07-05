<?php

	class CategoryAuth {

		protected $_cache = [];

		protected $_User;

		public function __construct(ForumsUserInterface $User) {
			$this->_User = $User;
		}

		/**
		 *
		 *
		 * @param string $format
		 * - 'short': [id1 => 'id1', id2 => 'id2']
		 * - 'select': [id1 => 'title 1'] for html select
		 * @return mixed
		 */
		public function getAllowed($format = 'short') {
			if (!empty($this->_cache[$format])) {
				return $this->_cache[$format];
			}

			$Category = ClassRegistry::init('Category');
			$acs = $this->_User->getMaxAccession();
			$categories = $Category->getCategoriesForAccession($acs);

			switch ($format) {
				case 'short':
					$cIds = array_keys($categories);
					$categories = array_combine($cIds, $cIds);
					break;
			}

			$this->_cache[$format] = $categories;
			return $this->_cache[$format];
		}

		public function isAccessionAuthorized($accession) {
			return $accession <= $this->_User->getMaxAccession();
		}

	}