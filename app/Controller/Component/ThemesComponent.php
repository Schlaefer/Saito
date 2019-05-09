<?php

	App::uses('App', 'Core');
	App::uses('Folder', 'Utility');
	App::uses('Component', 'Controller');

	class ThemesComponent extends Component {

		protected $_config = null;

		protected $_applied = null;

		protected $_available = [];

		protected $_Controller = null;

		public function initialize(Controller $controller) {
			$this->_Controller = $controller;
		}

		/**
		 * Sets and/or gets current theme
		 *
		 * @param mixed $params name string or config array
		 * @return string current theme
		 * @throws InvalidArgumentException
		 * @throws UnexpectedValueException
		 */
		public function theme($params = null) {
			if (is_array($params)) {
				$this->_config = $params;
				$this->_setTheme($this->_getApplied());
			} elseif (is_string($params)) {
				$this->_setTheme($params);
			}
			if (empty($this->_applied)) {
				if ($params === null) {
					throw new UnexpectedValueException(
						sprintf(
							'Theme could not be determined: %s',
							print_r($params, true)
						)
					);
				} else {
					throw new InvalidArgumentException('Theme is not set yet.');
				}
			}
			return $this->_applied;
		}

		/**
		 * Gets currently applied theme
		 */
		protected function _getApplied() {
			if ($this->_applied) {
				return $this->_applied;
			}
			if ($this->_Controller->CurrentUser->isLoggedIn()) {
				$_userTheme = $this->_Controller->CurrentUser['user_theme'];
				if (in_array($_userTheme, $this->getAvailable())) {
						$this->_applied = $_userTheme;
				}
			}
			if (!$this->_applied) {
				$this->_applied = $this->_config['default'];
			}
			return $this->_applied;
		}

		public function setDefault() {
			$this->_setTheme($this->_config['default']);
		}

		protected function _setTheme($theme) {
			$this->_applied = $theme;
			$this->_Controller->theme = $this->_applied;
		}

		/**
		 * Gets available themes
		 *
		 * @return array
		 */
		public function getAvailable() {
			if (!$this->_available) {
				$this->_setAvailable();
			}
			return $this->_available;
		}

		/**
		 * Sets available themes
		 */
		protected function _setAvailable() {
			$_themesSubset = [];

			$this->_available = $this->themeDirs();

			// allowed themes for all users
			if (isset($this->_config['available']['all'])) {
				if ($this->_config['available']['all'] !== '*') {
					$_themesSubset = $this->_config['available']['all'];
				}
			} else {
				$_themesSubset = [$this->_config['default']];
			}

			// allowed user themes
			$_currentUserId = $this->_Controller->CurrentUser->getId();
			if (isset($this->_config['available']['users'][$_currentUserId])) {
				$_themesSubset = array_merge($_themesSubset,
						$this->_config['available']['users'][$_currentUserId]);
			}

			// filter themes
			if ($_themesSubset) {
				$this->_available = array_intersect($this->_available, $_themesSubset);
			}

			// default theme is always available
			array_unshift($this->_available, $this->_config['default']);

			// make sure default/every theme is in list only one time
			$this->_available = array_unique($this->_available);
		}

		/**
		 * Reads all available themes from disk
		 *
		 * @return array with Theme names
		 */
		public function themeDirs() {
			$_ThemeDir = new Folder(App::paths()['View'][0] . 'Themed');
			return $_ThemeDir->read()[0];
		}

	}
