<?php

	App::uses('BbcodeSettings', 'Lib/Bbcode');

	class DummyDataShell extends AppShell {

		public $uses = ['Entry', 'User'];

		protected $_Categories = null;

		protected $_Users = null;

		protected $_text = null;

		protected $_Threads = [];

		public function generate() {
			$n = (int)$this->in('Number of postings to generate?', null, 100);
			$ratio = (int)$this->in('Average answers per thread?', null, 10);
			$seed = $n / $ratio;

			$Bbs = BbcodeSettings::getInstance();
			$Bbs->set([
				'hashBaseUrl' => 'entries/view/',
				'atBaseUrl' => 'users/name/',
				'server' => Router::fullBaseUrl(),
				'webroot' => Router::fullBaseUrl()
			]);
			$this->Entry->SharedObjects['CurrentUser'] = new SaitoUser();

			for ($i = 0; $i < $n; $i++) {
				$newThread = $i < $seed;

				$user = $this->_randomUser();
				$this->Entry->CurrentUser->set($user);

				$entry = [
					'subject' => $i,
					'text' => rand(0, 1) ? $this->_randomText() : '',
					'user_id' => $user['id']
				];
				if ($newThread) {
					$entry['category'] = $this->_randomCategory();
				} else {
					$entry['pid'] = array_rand($this->_Threads);
				}
				$entry = $this->Entry->createPosting(['Entry' => $entry]);
				if (empty($entry)) {
					throw new RuntimeException('Could not create entry: ' . $entry);
				}

				$this->out('.', 0);
				if ($i > 1 && !($i % 50)) {
					$this->out(" $i/$n", 1);
				}

				$id = $entry['Entry']['id'];
				$this->_Threads[$id] = $id;
			}

			$this->out();
			$this->out("Generated $i postings.");
		}

		protected function _randomCategory() {
			if ($this->_Categories === null) {
				$this->_Categories = $this->Entry->Category->find('all',
					['recursive' => 0, 'fields' => ['id']]);
			}
			$id = array_rand($this->_Categories);
			return $this->_Categories[$id]['Category']['id'];
		}

		protected function _randomUser() {
			if ($this->_Users === null) {
				$this->_Users = $this->User->find('all', ['recursive' => 0, 'conditions' => ['activate_code' => 0]]);
			}
			$id = array_rand($this->_Users);
			return $this->_Users[$id]['User'];
		}

		protected function _randomText() {
			if (empty($this->_text)) {
				$this->_text = file_get_contents('http://loripsum.net/api/short/plaintext');
			}
			return $this->_text;
		}

	}

	class SaitoUser implements ArrayAccess {

		protected $_settings;

		public function getId() {
			return $this->_settings['id'];
		}

		public function getMaxAccession() {
			return 2;
		}

		public function isLoggedIn() {
			return true;
		}

		public function isAdmin() {
			return true;
		}

		public function mockUserType($type) {
			$MockedUser = clone $this;
			$MockedUser['user_type'] = $type;
			return $MockedUser;
		}

		public function getBookmarks() {
			return [];
		}

		public function set($data) {
			$this->_settings = $data;
		}

		public function offsetExists($offset) {
			return isset($this->_settings[$offset]);
		}

		public function offsetGet($offset) {
			return $this->_settings[$offset];
		}

		public function offsetSet($offset, $value) {
			$this->_settings[$offset] = $value;
		}

		public function offsetUnset($offset) {
			unset($this->_settings[$offset]);
		}
	}

