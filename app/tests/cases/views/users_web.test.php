<?php

App::import('Model', 'Setting');
App::import('Model', 'User');
App::import('Component', 'Security');

class TestOfUsers extends CakeWebTestCase {

	public $auth = '';

	/*

	function TestOfUsers(){
		$this->r = array (
				'Bob' => array (
						'username'	=> 'Bob',
						'user_type'	=> 'admin',
						'user_email'=>	'bob@example.com',
						'user_pw'		=> 'test',
						'user_pw_confirm'		=> 'test',
				),
				'Alice' => array (
						'username'	=> 'Alice',
						'user_type'	=> 'mod',
						'user_email'=>	'alice@example.com',
						'user_pw'		=> 'test',
						'user_pw_confirm'		=> 'test',
				),
				'Charles'	=> array (
						'username'	=> 'Charles',
						'user_type'	=> 'user',
						'user_email'=>	'charles@example.com',
						'user_pw'		=> 'test',
						'user_pw_confirm'		=> 'test',
				),

		);
		
//		$this->user =& new User();
//		$this->user->create();
//		$this->user->save(array('User' => $this->r['Charles']));

		$settings =& new Setting();
		$settings->load();
	}

	function  __destruct() {
//		if($this->user) $this->user->delete();

	}

	public function get($url) {
		$this->baseurl = $_SERVER['SERVER_NAME'].current(split("webroot", $_SERVER['PHP_SELF']));
		parent::get($this->auth.$this->baseurl.$url);
	}

	public function testView() {
		// if not logged in you shouldn't see user profiles
		$this->get('/users/view/1');
		$this->assertResponse(200);
		$expected = Configure::read('Saito.Settings.forum_name').' – Login'; 
		$this->assertTitle($expected);

//		$this->setField("data[User][username]", $this->r['Charles']['username']);
//    $this->setField("data[User][user_pw]", 'test' );
//		$this->clickSubmit("Einloggen");

//		debug($this->showSource());
	}

	public function testEdit() {
		// if not logged in you shouldn't edit user profiles
		$this->get('/users/edit/1');
		$this->assertResponse(200);
		$expected = Configure::read('Saito.Settings.forum_name').' – Login'; 
		$this->assertTitle($expected);

		// @td login check of only able to edit own profile

		// @td login as admin and check if able to edit all profiles
	}

	public function testIndex() {
		// if not logged in you shouldn't see user index 
		$this->get('/users/index');
		$this->assertResponse(200);
		$expected = Configure::read('Saito.Settings.forum_name').' – Login'; 
		$this->assertTitle($expected);
	}
	
	public function testRegister() {
		// test if user register form is accessable for non logged in users
		$this->get('/users/register');
		$this->assertResponse(200);
		$this->assertField('data[User][username]', '');
		$this->assertField('data[User][user_email]', '');
		$this->assertField('data[User][user_pw]', '');
		$this->assertField('data[User][user_pw_confirm]', '');
	}

	public function testLogin() {
		$this->get('/users/login');
		$this->assertField('data[User][username]', '');
		$this->assertField('data[User][user_pw]', '');
	}

*/

}

?>