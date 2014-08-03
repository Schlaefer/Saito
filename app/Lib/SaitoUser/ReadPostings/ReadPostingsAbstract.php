<?php

	/**
	 * Handles read postings for the current users
	 */
	abstract class ReadPostingsAbstract {

		/**
		 * @var CurrentuserComponent
		 */
		protected $_CurrentUser;

		/**
		 * @var LastRefreshAbstract
		 */
		protected $_LastRefresh;

		protected $_modelAlias = 'Entry';

		/**
		 * array in which keys are ids of read postings
		 *
		 * @var array [<id-1> => 1, <id-2> => 1]
		 */
		protected $_readPostings = null;

		public function __construct(CurrentuserComponent $CurrentUser) {
			$this->_CurrentUser = $CurrentUser;
			$this->_LastRefresh = $this->_CurrentUser->LastRefresh;
		}

		/**
		 * sets entry/entries as read for the current user
		 *
		 * @param $postings array single ['Entry' => []] or multiple [0 => ['Entry' => …]
		 */
		abstract public function set($postings);

		/**
		 * checks if entry is read by the current user
		 *
		 * if timestamp is provided it is checked against user's last refresh time
		 *
		 * @param int $id
		 * @param mixed $timestamp unix timestamp or timestamp string
		 * @return bool
		 */
		public function isRead($id, $timestamp = null) {
			if ($this->_readPostings === null) {
				$this->_get();
			}

			if (isset($this->_readPostings[$id])) {
				return true;
			}

			if ($timestamp === null) {
				return false;
			}

			return $this->_LastRefresh->isNewerThan($timestamp);
		}

		/**
		 * delete all read entries for the current user
		 */
		abstract public function delete();

		/**
		 *
		 * @param $postings
		 * @return array
		 * @throws InvalidArgumentException
		 */
		protected function _preparePostings($postings) {
			// wrap single posting
			if (isset($postings[$this->_modelAlias])) {
				$postings = [0 => $postings];
			}

			if (empty($postings)) {
				throw new InvalidArgumentException;
			}

			// performance: don't store entries covered by timestamp
			foreach ($postings as $k => $posting) {
				if ($this->_LastRefresh->isNewerThan($posting[$this->_modelAlias]['time'])) {
					unset($postings[$k]);
				}
			}

			if (!empty($postings)) {
				$postings = Hash::extract($postings, '{n}.' . $this->_modelAlias . '.id');
			}

			return $postings;
		}

		/**
		 * gets all read postings for the current user and puts them into $_readPostings
		 *
		 * @return array
		 */
		abstract protected function _get();

	}