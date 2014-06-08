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

		protected function _filterOutNonExisting($categories) {
			return array_intersect_key($categories, $this->_categories);
		}

		protected function _getCustom() {
			// add new categories to custom set
			//
			// [4 => true, 7 => '0'] + [4 => '4', 7 => '7', 13 => '13']
			// becomes
			// [4 => true, 7 => '0', 13 => '13']
			// with 13 => '13' trueish
			if (empty($this->_user['user_category_custom'])) {
				$this->_user['user_category_custom'] = [];
			}
			$custom = $this->_user['user_category_custom'] + $this->_categories;

			// then filter for zeros to get only the user categories
			//  [4 => true, 13 => '13']
			$custom = array_filter($custom);

			$custom = $this->_filterOutNonExisting($custom);

			$keys = array_keys($custom);
			return array_combine($keys, $keys);
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
				$type = 'single';
				$categories = $this->_filterOutNonExisting(
					[$this->_user['user_category_active'] => $this->_user['user_category_active']]
				);
			} elseif ($this->_isCustom()) {
				$type = 'custom';
				$categories = $custom;
			} else {
				$type = 'all';
				$categories = $this->_categories;
			}
			return [$categories, $type, $custom];
		}

	}
