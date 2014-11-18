<?php

	namespace App\Controller\Component;

	use Cake\Controller\Component;
	use Cake\Core\Configure;
	use Saito\Shouts\ShoutsDataTrait;

	class ShoutsComponent extends Component {

		use ShoutsDataTrait;

		public function setShoutsForView() {
			// @todo @performance only do if cache is not valid and html need to be rendered
			$this->_registry->getController()->set('shouts', $this->get());
		}

	}
