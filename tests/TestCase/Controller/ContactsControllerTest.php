<?php

	App::uses('Controller', 'Controller');
	App::uses('UsersController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib/Test');

	class ContactsControllerTestCase extends \Saito\Test\ControllerTestCase {

		use \Saito\Test\SecurityMockTrait;

		public $fixtures = [
			'app.category',
			'app.entry',
			'app.setting',
			'app.user',
			'app.user_block',
			'app.user_ignore',
			'app.user_online',
			'app.user_read'
		];

		public function testContactEmailCc() {
			$data = [
				'Message' => [
					'sender_contact' => 'fo3@example.com',
					'subject' => 'subject',
					'text' => 'text',
					'carbon_copy' => '1'
				]
			];

			$result = $this->testAction('/contacts/owner', [
				'data' => $data, 'method' => 'POST', 'return' => 'vars']);

			//# test cc email
			$headers = $result['email']['headers'];
			$this->assertContains('From: macnemo <system@example.com>', $headers);
			// empty space from Cake implementation and missing name
			$this->assertContains('To:  <fo3@example.com>', $headers);
			$this->assertNotContains('Sender:', $headers);
		}

		/**
		 * tests anonymous users views contact form to owner
		 */
		public function testContactOwnerByAnonShowForm() {
			$result = $this->testAction(
				'/contacts/owner',
				['method' => 'GET', 'return' => 'view']
			);

			//# anon users must enter his email address
			// keep matcher in sync with testContactOwnerByUserShowForm
			$matcher = [
				'tag' => 'input',
				'id' => 'MessageSenderContact'
			];
			$this->assertTag($matcher, $result);
		}

		/**
		 * tests anonymous sends contact form to owner with invalid email-address
		 */
		public function testContactOwnerByAnonSendInvalidEmail() {
			$data = [
				'Message' => [
					'sender_contact' => '',
					'subject' => 'Subject',
					'text' => 'text',
				]
			];
			$Users = $this->generate(
				'Contacts',
				['components' => ['SaitoEmail' => ['email']]]
			);

			$Users->SaitoEmail->expects($this->never())->method('email');

			$result = $this->testAction(
				'/contacts/owner',
				['data' => $data, 'method' => 'POST', 'return' => 'contents']
			);

			$this->assertContains(
				'"type":"error","channel":"form","element":"#MessageSenderContact"',
				$result
			);
		}

		/**
		 * tests anonymous user successfully sends contact form to owner
		 */
		public function testContactOwnerByAnonSendSuccess() {
			$data = [
				'Message' => [
					'sender_contact' => 'fo3@example.com',
					'subject' => 'subject',
					'text' => 'text',
				]
			];

			$result = $this->testAction(
				'/contacts/owner',
				['data' => $data, 'method' => 'POST', 'return' => 'vars']
			);

			// redirect after successful sending
			$this->assertRedirectedTo();

			//# test registration email
			$headers = $result['email']['headers'];
			// empty space from Cake implementation and missing name
			$this->assertContains('From:  <fo3@example.com>', $headers);
			$this->assertContains('To: contact@example.com', $headers);
			$this->assertContains('Sender: macnemo <system@example.com>', $headers);
		}

		/**
		 * tests registered users views contact form to owner
		 */
		public function testContactOwnerByUserShowForm() {
			$this->generate('Contacts');
			$this->_loginUser(3);
			$result = $this->testAction(
				'/contacts/owner',
				['method' => 'GET', 'return' => 'view']
			);

			//# registered user doesn't provide a email address
			// keep matcher in sync with testContactOwnerByUserShowForm
			$matcher = [
				'tag' => 'input',
				'id' => 'MessageSenderContact'
			];
			$this->assertNotTag($matcher, $result);
		}

		/**
		 * tests registered user sends contact form to owner
		 */
		public function testContactOwnerByUserSend() {
			$this->generate('Contacts');
			$this->_loginUser(3);
			$data = [
				'Message' => [
					// should be ignored
					'sender_contact' => 'fo3@example.com',
					'subject' => 'subject',
					'text' => 'text',
				]
			];

			$result = $this->testAction(
				'/contacts/owner',
				['data' => $data, 'method' => 'POST', 'return' => 'vars']
			);

			// redirect after successful sending
			$this->assertRedirectedTo();

			//# test registration email
			$headers = $result['email']['headers'];
			// empty space from Cake implementation and missing name
			$this->assertContains('From: Ulysses <ulysses@example.com>', $headers);
			$this->assertContains('To: contact@example.com', $headers);
			$this->assertContains('Sender: macnemo <system@example.com>', $headers);
		}

		public function testContactUserByAnon() {
			$this->setExpectedException('BadRequestException');
			$this->testAction('/contacts/user/3');
		}

		public function testContactUserByUserNoId() {
			$this->generate('Contacts');
			$this->_loginUser(3);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/contacts/user/');
		}

		public function testContactNoSubject() {
			$data = array(
				'Message' => [
					'sender_contact' => 'fo3@example.com',
					'subject' => '',
					'text' => 'text',
				]
			);
			$Users = $this->generate('Contacts',
				['components' => ['SaitoEmail' => ['email']]]);
			$Users->SaitoEmail->expects($this->never())->method('email');
			$result = $this->testAction('/contact/owner/',
				['data' => $data, 'method' => 'post', 'return' => 'contents']);
			$this->assertContains(
				'"type":"error","channel":"form","element":"#MessageSubject"',
				$result
			);
		}

		/**
		 * tests contacting user with contacting disabled fails
		 */
		public function testContactUserContactDisabled() {
			$this->generate('Contacts');
			$this->_loginUser(2);

			$this->setExpectedException('InvalidArgumentException');
			$this->testAction('/contacts/user/5');
		}

		public function testContactUserWhoDoesNotExist() {
			$this->generate('Contacts');
			$this->_loginUser(2);

			$this->setExpectedException('InvalidArgumentException');
			$this->testAction('/contacts/user/9999');
		}

	}