<?php

	App::uses('ReadPostingsAbstract', 'Lib/SaitoUser/ReadPostings');
	App::uses('SaitoUserCookieStorage', 'Lib/SaitoUser/Cookies');

	/**
	 * Handles read posting by a client side cookie. Used for non logged-in users.
	 */
	class ReadPostingsCookie extends ReadPostingsAbstract {

		/**
		 * Max number of postings in cookie
		 */
		protected $_maxPostings = 240;

		protected $_Cookie;

		public function __construct(CurrentUserComponent $CurrentUser) {
			parent::__construct($CurrentUser);
			$this->_Cookie = new SaitoUserCookieStorage(
				$this->_CurrentUser->Cookie,
				'Read'
			);
		}

		public function set($entries) {
			$entries = $this->_preparePostings($entries);
			if (empty($entries)) {
				return;
			}

			$entries = array_fill_keys($entries, 1);
			$new = $this->_get() + $entries;
			if (empty($new)) {
				return;
			}
			$this->_readPostings = $new;

			$this->_gc();

			// make simple string and don't encrypt it to keep cookie small enough
			// to fit $this->_maxPostings into 4 kB
			$data = implode('.', array_keys($this->_readPostings));
			$this->_Cookie->encrypt = false;
			$this->_Cookie->write($data);
		}

		public function delete() {
			$this->_Cookie->delete();
		}

		/**
		 * limits the number of postings saved in cookie
		 *
		 * cookie size should not exceed 4 kB
		 */
		protected function _gc() {
			$overhead = count($this->_readPostings) - $this->_maxPostings;
			if ($overhead < 0) {
				return;
			}
			ksort($this->_readPostings);
			$this->_readPostings = array_slice($this->_readPostings, $overhead, null, true);
		}

		protected function _get() {
			if ($this->_readPostings !== null) {
				return $this->_readPostings;
			}
			$this->_readPostings = $this->_Cookie->read();
			if (empty($this->_readPostings)
					|| !preg_match('/^[0-9\.]*$/', $this->_readPostings)
			) {
				$this->_readPostings = [];
			} else {
				$this->_readPostings = explode('.', $this->_readPostings);
				$this->_readPostings = array_fill_keys($this->_readPostings, 1);
			}
			return $this->_readPostings;
		}

	}
