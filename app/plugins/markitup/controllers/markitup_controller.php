<?php
class MarkitupController extends Controller {
	public $components = null;
	public $helpers = array('Markitup.Markitup');
	public $layout = 'ajax';
	public $uses = null;
	public function preview($parser = '') {
		$content = $this->data;
		$this->set(compact('content', 'parser'));
	}
}
?>