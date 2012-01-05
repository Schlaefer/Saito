<?php
/* Users Test cases generated on: 2010-07-08 17:07:25 : 1278603025*/
App::import('Controller', 'Users');

require_once '_saito_controller_test_case.php';

class TestUsersController extends UsersController {
	public $name = 'Users';
//	var $autoRender = false;

	public $redirectUrl = null;
	public $renderedAction = null;

	function render($action = null, $layout = null, $file = null) {
		$this->renderedAction = $action;
		}

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class UsersControllerTestCase extends SaitoControllerCakeTestCase {
	var $fixtures = array('app.user', 'app.user_online', 'app.entry', 'app.category', 'app.smiley', 'app.smiley_code', 'app.setting', 'app.upload');
	public $name = 'Users';

	public function testLogin() {

		//* user sees login form
		$this->_prepareAction('/users/login');
		$result = $this->Users->login();
		$this->assertNull($this->Users->redirectUrl);

		return;

		//* users logged in
		$this->Users->Session->write('Auth.User', array(
        'id' => 3,
        'username' => 'Ulysses',
    ));

		//registred user before login try
		$registeredUsersBeforeLogin = $this->Users->User->find('count');

		$this->_prepareAction('/users/login');
		$timeOfLogin = date('Y-m-d H:i:s');
		$this->Users->login();
		$this->Users->User->id = 3;
		$userAfterLogin = $this->Users->User->read();

		// redirect 
		$this->assertEqual($this->Users->redirectUrl, $this->Users->referer());
		// user has to be in useronline
		$this->assertTrue($this->Users->User->UserOnline->findByUserId(3));

		// time is stored as last login time 
		$this->assertEqual($timeOfLogin, $userAfterLogin['User']['last_login']);

//		debug($this->Users->User->read());

		// check that there was no false insertion of new users through relationships
		// leave this test of the end of testLogin()
		$registeredUsersAfterLogin = $this->Users->User->find('count');
		$this->assertEqual($registeredUsersBeforeLogin, $registeredUsersAfterLogin);

//		debug($this->Users->data);
//		debug($this->Users->viewVars);
//		debug($this->Users->Auth->user());
//		debug($this->Users->renderedAction);
//		debug($this->Users->redirectUrl);
//		debug($this->Users->currentUser);
//		debug($result);


	}

	public function testView() {
		/*
		 * unregistred users can't see user profiles
		 */
		$this->_prepareAction('/users/view/1');
		$this->Users->view(1);
		$this->assertEqual($this->Users->redirectUrl, '/users/login');
		$this->Users->redirectUrl = NULL;

		/*
		 * registred users can see user profiles
		 */
		$this->_loginUser(3);
		$this->_prepareAction('/users/view/1');
		$this->Users->view(1);
		$this->assertNull($this->Users->redirectUrl);
		$result = $this->testAction('/users/view/1', array( 'return' => 'vars' ));
		$this->assertEqual($result['user']['User']['id'], 1);
		$this->assertEqual($result['user']['User']['username'], 'Alice');

		/*
		 * if user (profile) doesn't exist
		 */
		$this->_prepareAction('/users/view/9999');
		$this->Users->view(9999);
		$this->assertEqual($this->Users->redirectUrl, '/');
		$this->Users->redirectUrl = NULL;
	}

	function testIndexGetViewVarsRegistred() {
		/*
		$this->_loginUser("Charles");
		foreach ($this->r as $name => $userdata) {
			$this->prepareAction('/users/view', $userdata['user_id']);
			$result = $this->testAction('/users/view/'.$userdata['user_id'], array( 'return' => 'vars' ));
			$fields =  array_keys($userdata);
			foreach($fields as $field) {
				$this->assertEqual($result['user']['User'][$field], $userdata[$field]);
			}
		}
		*/
	}

	function testGetViewVarsChangepassword() {
		/*
		$this->_loginUser("Charles");
		$this->prepareAction('/users/changepassword', $this->cu['user_id']);
		$result = $this->testAction('/users/changepassword/'.$this->cu['user_id'], array( 'return' => 'vars' ));
		$this->assertTrue($result['allowedToEditUserData']);
		*/
		}

// --------------------------------------------------

	function startCase() {
	}

	function endCase() {
	}

	function startTest($message) {
		echo "<h3>Starting ".get_class($this)."->$message()</h3>\n";
		$this->Users = new TestUsersController();
		$this->Users->constructClasses();
	}

	function endTest($message) {
		$this->Users->Session->destroy();
		unset($this->Users);
		ClassRegistry::flush();
	}

}
?>