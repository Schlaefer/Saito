<?php

	namespace Saito\View\Cell;

	use Cake\View\Cell;
	use Saito\App\Registry;

	abstract class SlidetabCell extends Cell {

		public function __toString() {
			$this->_prepareRendering();
			$string = parent::__toString();
			return $string;
		}

		/**
		 * @return string
		 */
		abstract protected function _getSlidetabId();

		protected function _prepareRendering() {
			$CurrentUser = Registry::get('CU');
			$slidetabId = $this->_getSlidetabId();

			if ($CurrentUser['show_' . $slidetabId] == 1) {
				$isOpen = true;
			} else {
				$isOpen = false;
			}

			$this->set(compact('CurrentUser', 'isOpen', 'slidetabId'));
		}

	}