<?php

	namespace Saito\Posting\Decorator;

	use Cake\Core\Configure;

	trait UserPostingTrait {

		protected $_cache = [];

		protected $_CU;

		public function getCurrentUser() {
			return $this->_CU;
		}

		public function setCurrentUser($CU) {
			$this->_CU = $CU;
		}

		/**
		 * Checks if answering an entry is allowed
		 *
		 * @return boolean
		 */
		public function isAnsweringForbidden() {
			if ($this->isLocked()) {
				return 'locked';
			}
            $resource = 'saito.core.category.' .$this->get('category')['id'] . '.answer';
            $permission = $this->_CU->permission($resource);
            return !$permission;
		}

		/**
		 * checks if entry is bookmarked by current user
		 */
		public function isBookmarked() {
			return $this->_CU->hasBookmarked($this->get('id'));
		}

		public function isEditingAsCurrentUserForbidden() {
			return $this->_isEditingForbidden($this, $this->_CU);
		}

		public function isEditingWithRoleUserForbidden() {
			return $this->_isEditingForbidden($this, $this->_CU->mockUserType('user'));
		}

		protected function _isEditingForbidden(\Saito\Posting\Posting $posting, $User) {
			if ($User->isLoggedIn() !== true) {
				return true;
			} elseif ($User->permission('saito.core.posting.edit.unrestricted')) {
				return false;
			}

            $editPeriod = Configure::read('Saito.Settings.edit_period') * 60;
            $timeLimit = $editPeriod + strtotime($posting->get('time'));
			$isOverTime = time() > $timeLimit;

            $isOwn = (int)$User->getId() === (int)$posting->get('user_id');

            if ($User->permission('saito.core.posting.edit.restricted')) {
                if($isOwn && $isOverTime && !$posting->isPinned()) {
                    return 'time';
                } else {
                    return false;
                }
            }

            if (!$isOwn) {
                return 'user';
            } elseif ($isOverTime) {
                return 'time';
            } elseif ($this->isLocked()) {
                return 'locked';
            }

            return false;
		}

		public function isIgnored() {
			return $this->_CU->ignores($this->get('user_id'));
		}

		public function isUnread() {
			if (!isset($this->_cache['isUnread'])) {
				$id = $this->get('id');
				$time = $this->get('time');
				$this->_cache['isUnread'] = !$this->_CU->ReadEntries->isRead($id, $time);
			}
			return $this->_cache['isUnread'];
		}

		/**
		 * Checks if posting has newer answers
		 *
		 * currently only supported for root postings
		 *
		 * @return bool
		 * @throws \RuntimeException
		 */
		public function hasNewAnswers() {
			if (!$this->isRoot()) {
				throw new \RuntimeException('Posting with id ' . $this->get('id') . ' is no root posting.');
			}
			if (!isset($this->_CU['last_refresh'])) {
				return false;
			}
			return $this->_CU['last_refresh_unix'] < strtotime($this->get('last_answer'));
		}

	}
