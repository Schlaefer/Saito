<?php

	namespace Saito\Posting\Decorator;

	trait PostingTrait {

		public function isLocked() {
			return $this->get('locked') != false;
		}

		public function isRoot() {
			return $this->get('pid') === 0;
		}

	}