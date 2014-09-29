<?php

	abstract class UserBlockerAbstract {

		protected $_Model;

		public abstract function block($userId, array $options = []);

		/**
		 * id for reason why user is blocked
		 *
		 * in plugin use <domain>.<id>
		 *
		 * @return string
		 */
		public abstract function getReason();

		public function setUserBlockModel($Model) {
			$this->_Model = $Model;
		}

	}
