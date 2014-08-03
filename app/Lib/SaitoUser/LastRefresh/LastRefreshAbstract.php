<?php

	/**
	 * handles last refresh time for the current user
	 */
	abstract class LastRefreshAbstract {

		/**
		 * @var CurrentUserComponent
		 */
		protected $_CurrentUser;

		/**
		 * @var int unix timestamp
		 */
		protected $_timestamp = null;

		public function __construct(CurrentuserComponent $CurrentUser) {
			$this->_CurrentUser = $CurrentUser;
		}

		/**
		 * is last refresh newer than $timestamp
		 *
		 * @param mixed $timestamp int unix-timestamp or date as string
		 * @return mixed bool or null if not determinable
		 */
		public function isNewerThan($timestamp) {
			if (is_string($timestamp)) {
				$timestamp = strtotime($timestamp);
			}
			$lastRefresh = $this->_get();
			// timestamp is not set (or readable): everything is considered new
			if ($lastRefresh === false) {
				return null;
			}
			return $lastRefresh > $timestamp;
		}

		/**
		 * returns last refresh timestamp
		 *
		 * @return mixed int if unix timestamp or bool false if uninitialized
		 */
		abstract protected function _get();

		/**
		 * @param mixed $timestamp
		 *
		 * null|'now'|<`Y-m-d H:i:s` timestamp>
		 */
		public function set($timestamp = null) {
			// all postings individually marked as read should be removed because they
			// are older than the new last-refresh timestamp
			$this->_CurrentUser->ReadEntries->delete();

			$this->_timestamp = $this->_parseTimestamp($timestamp);
			$this->_set();
		}

		protected abstract function _set();

		protected function _parseTimestamp($timestamp) {
			if ($timestamp === 'now' || $timestamp === null) {
				$timestamp = date('Y-m-d H:i:s');
			}
			return $timestamp;
		}

	}
