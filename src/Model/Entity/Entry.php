<?php

	namespace App\Model\Entity;

	use Cake\ORM\Entity;
	use Saito\App\Registry;
	use Saito\Posting\Decorator\PostingTrait;

	class Entry extends Entity {

		use PostingTrait;

		protected $_Posting;

		public function toPosting() {
			return Registry::newInstance(
				'\Saito\Posting\Posting', ['rawData' => $this->toArray()]
			);
		}

	}
