<?php

	App::uses('LastRefreshAbstract', 'Lib/SaitoUser/LastRefresh');

	/**
	 * handles last refresh time for current user via database
	 *
	 * used for logged-in users
	 */
	class LastRefreshDatabase extends LastRefreshAbstract {

		/**
		 * @return bool|int|mixed
		 * @throws Exception
		 */
		protected function _get() {
			if ($this->_timestamp === null) {
				// can't use ArrayIterator access because array_key_exists doesn't work
				// on ArrayIterator … Yeah for PHP!1!!
				$settings = $this->_CurrentUser->getSettings();
				if (!array_key_exists('last_refresh', $settings)) {
					throw new Exception('last_refresh not set');
				} elseif ($settings['last_refresh'] === null) {
					// mar is not initialized
					$this->_timestamp = false;
				} else {
					$this->_timestamp = $this->_CurrentUser['last_refresh_unix'];
				}
			}
			return $this->_timestamp;
		}

		protected function _set() {
			$this->_CurrentUser->_User->setLastRefresh($this->_timestamp);
			$this->_CurrentUser['last_refresh'] = $this->_timestamp;
		}

		public function setMarker() {
			$this->_CurrentUser->_User->setLastRefresh();
		}

		protected function _parseTimestamp($timestamp) {
			if ($timestamp === 'now') {
				$timestamp = date('Y-m-d H:i:s');
			} elseif ($timestamp === null) {
				$timestamp = $this->_CurrentUser['last_refresh_tmp'];
			}
			return $timestamp;
		}

	}
