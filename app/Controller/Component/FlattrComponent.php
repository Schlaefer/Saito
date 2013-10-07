<?php

	class FlattrComponent extends Component {

		public function beforeRender(Controller $Controller) {
			// ### find categories for flattr
			if ($Controller->action === 'add' || $Controller->action === 'edit') {
				$categoryFlattr = $Controller->Entry->Category->find(
					'list',
					array('conditions' => 'accession = 0', 'fields' => array('id'))
				);
				$Controller->set('category_flattr', $categoryFlattr);
			}
		}

	}
