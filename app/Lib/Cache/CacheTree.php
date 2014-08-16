<?php

	App::uses('ItemCache', 'Lib/Cache');

	class CacheTree extends ItemCache {

		/**
		 * Stores if an entry is cached and if the cache is valid for this request
		 *
		 * @var array
		 */
		protected $_validEntries = [];

		/**
		 * @var CurrentUserComponent
		 */
		protected $_CurrentUser;

		/**
		 * @var bool
		 */
		protected $_allowUpdate = false;

		/**
		 * @var bool
		 */
		protected $_allowRead = false;

		public function initialize(ForumsUserInterface $CurrentUser) {
			$this->_CurrentUser = $CurrentUser;

			if (Configure::read('debug') > 1) {
				Configure::write('Saito.Cache.Thread', false);
			}

			if (!Configure::read('Saito.Cache.Thread')) {
				return;
			}

			$this->_allowUpdate = true;
			$this->_allowRead = true;
		}

		public function isCacheUpdatable(array $entry) {
			if (!$this->_allowUpdate) {
				return false;
			}
			return $this->_isRead($entry);
		}

		public function isCacheValid(array $entry) {
			if (!$this->_allowRead) {
				return false;
			}

			$id = (int)$entry['id'];
			if (isset($this->_validEntries[$id])) {
				return $this->_validEntries[$id];
			}

			if (!$this->_inCache($entry)) {
				$valid = false;
			} elseif ($this->_isRead($entry)) {
				$valid = true;
			} else {
				$valid = false;
			}

			$this->_validEntries[$id] = $valid;
			return $valid;
		}

		/**
		 * @param array $entry
		 * @return bool
		 */
		protected function _isRead(array $entry) {
			return $this->_CurrentUser->LastRefresh->isNewerThan($entry['last_answer']) === true;
		}

		protected function _inCache($entry) {
			$id = $entry['id'];

			if (!$this->get($id)) {
				return false;
			}

			$lastAnswer = strtotime($entry['last_answer']);
			$hasNewerAnswers = $this->compareUpdated($id, $lastAnswer,
				function ($updated, $lastAnswer) {
					return $lastAnswer > $updated;
				});
			if ($hasNewerAnswers) {
				$this->delete($id);
				return false;
			}

			return true;
		}

		public function get($id = null) {
			if (!$this->_allowRead) {
				return false;
			}
			return parent::get($id);
		}

		public function set($id, $content, $timestamp = null) {
			if (!$this->_allowUpdate) {
				return false;
			}
			parent::set($id, $content, $timestamp);
		}

	}