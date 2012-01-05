<?php

class CompleteWebTestCase extends CakeWebTestCase {

	function CompleteWebTestCase(){
		// constructor
		$this->baseurl = current(split("webroot", $_SERVER['PHP_SELF']));
	}

}

?>