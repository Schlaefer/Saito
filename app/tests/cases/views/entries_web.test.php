<?php

App::import('Model', 'Setting');
App::import('Helper', 'Timer');

class TestOfEntries extends CakeWebTestCase {

	public $auth = '';

	/*
	public function testIndex() {
		$this->get("/");
		$this->assertTitle(Configure::read('Saito.Settings.forum_name'));
	}

	public function testView() {
	}

	public function testEdit() {
		// if not logged in you shouldn't edit postings
		$this->get('/entries/edit/1');
		$this->assertResponse(200);
		$expected = Configure::read('Saito.Settings.forum_name').' – Login'; 
		$this->assertTitle($expected);
	}

	public function testAdd() {
		// if not logged in you shouldn't start new threads
		$this->get('/entries/add');
		$this->assertResponse(200);
		$expected = Configure::read('Saito.Settings.forum_name').' – Login'; 
	}

	public function testSearch() {
		// if not logged in you are not allowed to search
		$this->get('/entries/search');
		$this->assertResponse(200);
		$this->assertTitle(Configure::read('Saito.Settings.forum_name'));
	}
	 * 
	 */

	/*** setup ***/

	function TestOfEntries(){
		$settings =& new Setting();
		$settings->load();
	}

	public function get($url) {
		$this->baseurl = $_SERVER['SERVER_NAME'].current(split("webroot", $_SERVER['PHP_SELF']));
		parent::get($this->auth.$this->baseurl.$url);
	}

}

?>