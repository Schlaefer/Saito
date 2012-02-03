<?php

class FlattrComponent extends Object {

	public function beforeRender(&$controller) {
		// ### find categories for flattr
		if ($controller->action === 'add' || $controller->action === 'edit') {
			$category_flattr = $controller->Entry->Category->find( 'list', array( 'conditions' => 'accession = 0', 'fields'=> array('id')));
			$controller->set( 'category_flattr', $category_flattr );
		}
	}

}
?>