<?php

	namespace Saito\User;

	/**
	 * Class Bookmarks handles bookmarks for a CurrentUser
	 */
	class Bookmarks {

		/**
		 * @var bookmarks format: [entry_id => id, …]
		 */
		protected $_bookmarks;

		protected $_CurrentUser;

		public function __construct(\CurrentUserComponent $CurrentUser) {
			$this->_CurrentUser = $CurrentUser;
		}

		public function isBookmarked($entryId) {
			if (!$this->_CurrentUser->isLoggedIn()) {
				return false;
			}
			if ($this->_bookmarks === null) {
				$this->_get();
			}
			return isset($this->_bookmarks[$entryId]);
		}

		protected function _get() {
			if ($this->_bookmarks !== null) {
				return $this->_bookmarks;
			}
			$this->_bookmarks = [];
			if (!$this->_CurrentUser->isLoggedIn() === false) {
				$bookmarks = $this->_CurrentUser->_User->Bookmark->findAllByUserId(
					$this->_CurrentUser->getId(),
					['contain' => false]
				);
				if (!empty($bookmarks)) {
					foreach ($bookmarks as $bookmark) {
						$this->_bookmarks[(int)$bookmark['Bookmark']['entry_id']] = (int)$bookmark['Bookmark']['id'];
					}
				}
			}
			return $this->_bookmarks;
		}

	}
