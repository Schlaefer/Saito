<?php

	App::uses('BbcodeSettings', 'Lib/Bbcode');
	App::uses('SaitoUser', 'Lib/SaitoUser');

	class DummyDataShell extends AppShell {

		public $uses = ['Entry', 'User'];

		protected $_Categories = null;

		protected $_Users = null;

		protected $_text = null;

		protected $_Threads = [];

		protected $_users = ['aaron', 'Alex', 'Amy', 'Ana-Lucia', 'Anthony', 'Ben',
			'Bernard', 'Boone', 'Carmen', 'Carole', 'Charles', 'Charlie', 'Charlotte',
			'Christian', 'Claire', 'Daniel', 'Danielle', 'Desmond', 'Dogen', 'Eko',
			'Eloise', 'Ethan', 'Frank', 'Frogurt', 'George', 'Gina', 'Horace', 'Hugo',
			'Ilana', 'Jack', 'Jacob', 'James', 'Jin', 'John', 'Juliet', 'Kate',
			'Kelvin', 'Liam', 'Libby', 'Martin', 'Maninbla', 'Michael', 'Michelle',
			'Miles', 'Nadia', 'Naomi', 'Nikki', 'Omar', 'Paulo', 'Penny', 'Pierre',
			'Richard', 'Sarah', 'Sayid', 'Shannon', 'Stuart', 'Sun', 'Teresa', 'Tom',
			'walt'];

		public function main() {
			$this->user();
			$this->generate();
		}

		public function generate() {
			$n = (int)$this->in('Number of postings to generate?', null, 100);
			if ($n === 0) {
				return;
			}
			$ratio = (int)$this->in('Average answers per thread?', null, 10);
			$seed = $n / $ratio;

			$Bbs = BbcodeSettings::getInstance();
			$Bbs->set([
				'hashBaseUrl' => 'entries/view/',
				'atBaseUrl' => 'users/name/',
				'server' => Router::fullBaseUrl(),
				'webroot' => Router::fullBaseUrl()
			]);
			$this->Entry->SharedObjects['CurrentUser'] = new SaitoUserDummy();

			for ($i = 0; $i < $n; $i++) {
				$newThread = $i < $seed;

				$user = $this->_randomUser();
				$this->Entry->CurrentUser->setSettings($user);

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

				$this->_progress($i, $n);

				$id = $entry['Entry']['id'];
				$this->_Threads[$id] = $id;
			}

			$this->out();
			$this->out("Generated $i postings.");
		}

		public function user() {
			$max = count($this->_users);
			$n = (int)$this->in("Number of users to generate (max: $max)?", null, 0);
			if ($n === 0) {
				return;
			}
			if ($n > $max) {
				$n = $max;
			}
			$users = array_rand($this->_users, $n);
			$i = 0;
			foreach ($users as $user) {
				$name = $this->_users[$user];
				$data = [
					'User' => [
						'username' => $name,
						'password' => 'test',
						'password_confirm' => 'test',
						'user_email' => "$name@example.com"
					]
				];
				$this->User->register($data, true);
				$this->_progress($i++, $n);
			}

			$this->out();
			$this->out("Generated $i users.");

		}

		protected function _progress($i, $off) {
			if ($i < 1) {
				return;
			}
			$this->out('.', 0);
			if ($i > 1 && !($i % 50)) {
				$percent = (int)floor($i/$off * 100);
				$this->out(sprintf(' %3s%%', $percent), 1);
			}
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
				$this->_Users = $this->User->find('all',
					['recursive' => -1, 'conditions' => ['activate_code' => 0]]);
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

	class SaitoUserDummy extends SaitoUser {

		public function __construct($settings = null) {
			parent::__construct($settings);
			App::uses('CategoryAuth', 'Lib/SaitoUser');
			$this->Categories = new CategoryAuth($this);
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

		public function hasBookmarked() {
			return false;
		}

	}

