<?php
App::import('Lib','stabref.stabref');

/**
 * Description of stab_ref
 *
 * @author siezi
 */
class StabrefHelper extends AppHelper {
	public $lastAction 				= NULL;
	public $lastController 		= NULL;

	protected $stabref;

	public $helpers = array (
			'Session',
	);

	public function  __construct() {
		parent::__construct();
	}
	public function beforeRender() {
		$this->stabref = new Stabref();
	 	$this->stabref->read($this->Session);
		$this->lastAction = $this->stabref->lastAction;
		$this->lastController = $this->stabref->lastController;
	}
}
?>
