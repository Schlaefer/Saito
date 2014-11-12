<?php

	namespace Saito\Posting\Decorator;

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
		 * @param array $entry
		 * @return boolean
		 */
		public function isAnsweringForbidden() {
			if ($this->isLocked()) {
				$isAnsweringForbidden = 'locked';
			} else {
				$isAnsweringForbidden = false;
			}
			return $isAnsweringForbidden;
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
			// Anon
			if ($User->isLoggedIn() !== true) {
				return true;
			}

			// Admins
			if ($User->isAdmin()) {
				return false;
			}

			$verboten = true;

			$timeLimit = (\Configure::read('Saito.Settings.edit_period') * 60) + strtotime($posting->get('time'));
			$isOverTimeLimit = time() > $timeLimit;

			$isUsersPosting = (int)$User->getId() === (int)$posting->get('user_id');

			if ($User->isMod()) {
				// Mods
				// @todo mods don't edit admin posts
				if ($isUsersPosting && $isOverTimeLimit &&
					/* Mods should be able to edit their own posts if they are pinned
					 *
					 * @todo this opens a 'mod can pin and then edit root entries'-loophole,
					 * as long as no one checks pinning for Configure::read('Saito.Settings.edit_period') * 60
					 * for mods pinning root-posts.
					 */
					(!$posting->isPinned())
				) {
					// mods don't mod themselves
					$verboten = 'time';
				} else {
					$verboten = false;
				};

			} else {
				// Users
				if ($isUsersPosting === false) {
					$verboten = 'user';
				} elseif ($isOverTimeLimit) {
					$verboten = 'time';
				} elseif ($this->isLocked()) {
					$verboten = 'locked';
				} else {
					$verboten = false;
				}
			}

			return $verboten;
		}

		public function isIgnored() {
			return $this->_CU->ignores($this->get('user_id'));
		}

		public function isNew() {
			if (isset($this->_cache['isNew'])) {
				$this->_cache['isNew'] = $this->_cache['isNew'];
			} else {
				$id = $this->get('id');
				$time = $this->get('time');
				$this->_cache['isNew'] = !$this->_CU->ReadEntries->isRead($id, $time);
			}
			return $this->_cache['isNew'];
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
