<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class SaitoControllerCakeTestCase extends CakeTestCase {

	protected function _prepareAction($url,$id = null) {
		if ( $this->name == false ) {
			throw new InvalidArgumentException('$this->name not set for Test Case');
			}
		$this->{$this->name}->params = Router::parse($url . ($id !== null ? "/$id" : ''));
		$this->{$this->name}->Component->initialize($this->{$this->name});

//		$this->{$this->name}->Session->start();
//		$this->{$this->name}->Session->id('test');

		$this->{$this->name}->beforeFilter();
    $this->{$this->name}->Component->startup($this->{$this->name});


	}

	protected function _loginUser($id) {
		if ( $this->name == false ) {
			throw new InvalidArgumentException('$this->name not set for Test Case');
			}
		$this->users = $this->_fixtures['app.user']->records;
		$this->cu = $this->users[$id-1];
		$this->{$this->name}->Session->write('Auth.User', $this->cu);
	}
}
?>