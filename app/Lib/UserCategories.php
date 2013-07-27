<?php

	class UserCategories {

		protected $_user;
		protected $_categories;

		/**
		 * @param array $user
		 * @param array $categories
		 */
		public function __construct(array $user, array $categories) {
			$this->_user = $user;
			$this->_categories = $categories;
		}

		protected function _isAll() {
			return (int)$this->_user['user_category_active'] === -1;
		}

		protected function _isSingle() {
			return (int)$this->_user['user_category_active'] > 0;
		}

		protected function _isCustom() {
			return (int)$this->_user['user_category_active'] == 0 &&
			empty($this->_user['user_category_custom']) === false;
		}

		protected function _getCustom() {
			// merge all-cats onto user-cats to include categories which are
			// new since the user updated his user-cats the last time
			//
			// [4 => '4', 7 => '7', 13 => '13'] + [4 => true, 7 => '0']
			// becomes
			// [4 => true, 7 => '0', 13 => '13']
			// with 13 => '13' trueish
			$custom = $this->_user['user_category_custom'] + $this->_categories;
			// then filter for zeros to get only the user categories
			//  [4 => true, 13 => '13']
			$custom = array_filter($custom);
			$custom = array_intersect_key($custom, $this->_categories);
			return $custom;
		}

		/**
		 * @return array
		 *
		 * $categories: array with categories for the active type [cat_id1, cat_id2, …]
		 * $type: active type: 'all', 'single' or 'custom'
		 * $custom: categories for 'custom' [cat_id1, cat_id2, …]
		 */
		public function get() {
			$custom = $this->_getCustom();
			if ($this->_isSingle()) {
				$type       = 'single';
				$categories = array_intersect_key(
					$this->_categories,
					[$this->_user['user_category_active'] => 1]
				);
			} elseif ($this->_isCustom()) {
				$type       = 'custom';
				$categories = array_keys($custom);
			} else {
				$type = 'all';
				$categories =  $this->_categories;
			}
			return [$categories, $type, $custom];
		}
	}

