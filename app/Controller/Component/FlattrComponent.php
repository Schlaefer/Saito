<?php

class FlattrComponent extends Component {

	public function beforeRender(Controller $Controller) {
		// ### find categories for flattr
		if ($Controller->action === 'add' || $Controller->action === 'edit') {
			$category_flattr = $Controller->Entry->Category->find( 'list', array( 'conditions' => 'accession = 0', 'fields'=> array('id')));
			$Controller->set( 'category_flattr', $category_flattr );
		}
	}

}
?>