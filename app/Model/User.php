<?php

	App::uses('AppModel', 'Model');
	App::uses('CakeEvent', 'Event');

	/**
	 * Authentication methods
	 */
	App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
	App::uses('MlfPasswordHasher', 'Controller/Component/Auth');
	App::uses('Mlf2PasswordHasher', 'Controller/Component/Auth');

	/**
	 * @td verify that old pw is needed for changing pw(?) [See user.test.php both validatePw tests]
	 */
	class User extends AppModel {

		public $name = 'User';
		public $actsAs = array('Containable');
		public $hasOne = array(
				'UserOnline' => array(
						'className'	 => 'UserOnline',
						'foreignKey' => 'user_id',
						'conditions' => array(
							'UserOnline.user_id REGEXP "^-?[0-9]+$"',
						),
				),
		);
		public $hasMany = array(
				'Bookmark' => array(
						'foreignKey'		 => 'user_id',
						'dependent'			 => true,
				),
				'Esnotification' => array(
						'foreignKey' => 'user_id',
				),
				'Entry'			 => array(
						'className'	 => 'Entry',
						'foreignKey' => 'user_id',
				),
				'Upload'		 => array(
						'className'	 => 'Upload',
						'foreignKey' => 'user_id',
				),
		);
		public $validate = array(
				'username' => array(
						'isUnique' => array(
								'rule'		 => 'isUnique',
								'last'		 => 'true',
						),
						'notEmpty' => array(
								'rule'			 => 'notEmpty',
								'last'			 => 'true',
						),
				),
				'user_type'	 => array(
						'allowedChoice' => array(
								'rule' => array('inList', array('user', 'admin', 'mod')),
						),
				),
				'password' => array(
						'notEmpty' => array(
								'rule'			 => 'notEmpty',
								'last'			 => 'true',
						),
						'pwConfirm'	 => array(
								'rule' => array('validateConfirmPassword'),
								'last'						 => 'true',
								'message'					 => 'validation_error_pwConfirm',
						),
				),
				'password_confirm' => array(
				),
				'password_old' => array(
						'notEmpty' => array(
								'rule'			 => 'notEmpty',
								'last'			 => 'true',
						),
						'pwCheckOld' => array(
								'rule' => array('validateCheckOldPassword'),
								'last'			 => 'true',
								'message'		 => 'validation_error_pwCheckOld',
						),
				),
				'user_email' => array(
						'isUnique' => array(
								'rule'		 => 'isUnique',
								'last'		 => 'true',
						),
						'isEmail'	 => array(
								'rule' => array('email', true),
								'last'			 => 'true',
						)
				),
				'hide_email' => array(
						'rule' => array('boolean'),
				),
				# @td we don't use this field yet
				'logins' => array(
						'rule'			 => 'numeric',
				),
				'registered' => array(
				),
				'user_view' => array(
						'allowedChoice' => array(
								'rule' => array('inList', array('thread', 'mix', 'board')),
						),
				),
				'new_postin_notify' => array(
						'rule' => array('boolean'),
				),
				'personal_messages' => array(
						'rule' => array('boolean'),
				),
				'time_difference' => array(
				),
				# User durch admin/mod gesperrt?
				'user_lock' => array(
						'rule' => array('boolean'),
				),
				/*
				 * password forgotten code
				 *
				 * store temporary md5 code after password is send
				 */
				'pwf_code' => array(
				),
				'activate_code' => array(
						'numeric' => array(
								'rule'			 => 'numeric',
								'allowEmpty' => false,
						),
						'between'		 => array(
								'rule' => array('between', 0, 9999999),
						),
				),
				'user_font_size' => array(
						'rule'								 => 'numeric',
				),
				'user_signatures_hide' => array(
						'rule' => array('boolean'),
				),
				'user_signature_images_hide' => array(
						'rule' => array('boolean'),
				),
				'user_forum_refresh_time' => array(
						'numeric' => array(
								'rule'				 => 'numeric',
						),
						'greaterNull'	 => array(
								'rule' => array('comparison', '>=', 0),
						),
						'maxLength' => array(
								'rule' => array('maxLength', 3),
						),
				),
				'user_forum_hr_ruler' => array(
						'rule' => array('boolean'),
				),
				'user_automaticaly_mark_as_read' => array(
						'rule' => array('boolean'),
				),
				'user_sort_last_answer' => array(
						'rule' => array('boolean'),
				),
				'user_show_own_signature' => array(
						'rule' => array('boolean'),
				),
				'user_color_new_postings' => array(
						'rule'										 => '/^#?[a-f0-9]{0,6}$/i',
				),
				'user_color_old_postings'	 => array(
						'rule'											 => '/^#?[a-f0-9]{0,6}$/i',
						'message'										 => '*',
				),
				'user_color_actual_posting'	 => array(
						'rule'						 => '/^#?[a-f0-9]{0,6}$/i',
				),
		);
		protected $fieldsToSanitize = array(
				'user_hp',
				'user_place',
				'user_email',
// Wenn @mlf sollte, wenn die Performance es zulässt, der Name sowieso nicht in
// der `entries` Tabelle stehen, sondern sauber über die `User.id` Verbindung
// aus der `User` Tabelle entnommen werden. Dies ist im Moment schon der Fall,
// so dass dieses Feld @mlf entfernt werden kann und damit auch wieder dieser Hack.
// @td validate input for username [a-z][A-Z][0-9][_-]
//		'username',
				'signature',
				'profile',
		);

		/**
		 * True if registerGc garbage collection has ran
		 *
		 * registerGc is triggered in beforeFind(). To don't trigger an infinite
		 * call-loop we set it running here when it's started for the first time
		 *
		 * @var bool
		 */
		protected $_registerGcHasRun = false;

		public function setLastRefresh($lastRefresh = null) {
			Stopwatch::start('Users->setLastRefresh()');
			$data[$this->alias]['last_refresh_tmp'] = date("Y-m-d H:i:s");

			if ($lastRefresh) {
				$data[$this->alias]['last_refresh'] = $lastRefresh;
			}

			$this->contain();
			if ($this->save($data, true, array('last_refresh_tmp', 'last_refresh')) == false) {
				throw new Exception("Updating last user refresh failed.");
			}
			Stopwatch::end('Users->setLastRefresh()');
		}

		public function numberOfEntries() {
			/*
			  # @mlf change after mlf is gone, we only use `entry_count` then
			  $count = $this->data['User']['entry_count'];
			  if ( $count == 0 )
			 */ {
				$count = $this->Entry->find('count',
						array(
						'contain'		 => false,
						'conditions' => array('Entry.user_id' => $this->id),
						)
				);
			}
			return $count;
		}

		public function incrementLogins($id, $amount = 1) {
			$data = [
				$this->alias => [
					'id'         => $id,
					'logins'     => $this->field('logins') + $amount,
					'last_login' => date('Y-m-d H:i:s')
				]
			];
			if ($this->save($data, true, ['logins', 'last_login']) == false) {
				throw new Exception('Increment logins failed.');
			}
		}

		/**
		 * Removes a user and all his data execpt for his entries
		 *
		 * @param int $id user-ID
		 * @return boolean
		 */
		public function deleteAllExceptEntries($id) {
			if ($id == 1)
				return false;

			$success = true;
			$success = $success && $this->Upload->deleteAllFromUser($id);
			$success = $success && $this->Esnotification->deleteAllFromUser($id);
			$success = $success && $this->Entry->anonymizeEntriesFromUser($id);
			$success = $success && $this->UserOnline->deleteAll(
							array('user_id'	 => $id), false);
			$success = $success && $this->delete($id, true);
			return $success;
		}

		public function autoUpdatePassword($id, $password) {
			$this->contain();
			$data = $this->read(null, $id);
			$oldPassword = $data[$this->alias]['password'];
			$blowfishHashIdentifier = '$2a$';
			if (strpos($oldPassword, $blowfishHashIdentifier) !== 0):
				$this->saveField('password', $password);
			endif;
		}

		public function beforeFind($queryData) {
			if ($this->_registerGcHasRun === false) {
				$this->_registerGc();
			}
			return parent::beforeFind($queryData);
		}

		public function afterFind($results, $primary = false) {
			$results = parent::afterFind($results, $primary);

			if (isset($results[0][$this->alias]) && array_key_exists('user_color_new_postings',
							$results[0][$this->alias])) {
				//* @td refactor this shit
				if (empty($results[0][$this->alias]['user_color_new_postings'])) {
					$results[0][$this->alias]['user_color_new_postings'] = '#';
					$results[0][$this->alias]['user_color_old_postings'] = '#';
					$results[0][$this->alias]['user_color_actual_posting'] = '#';
				}
			}

			if (isset($results[0][$this->alias]) && isset($results[0][$this->alias]['user_category_custom'])) {
				if (empty($results[0][$this->alias]['user_category_custom'])) {
					$results[0][$this->alias]['user_category_custom'] = array();
				} else {
					$results[0][$this->alias]['user_category_custom'] =
							unserialize($results[0][$this->alias]['user_category_custom']);
				}
			}

			# @td font-size
			if (isset($results[0][$this->alias]) && array_key_exists('user_font_size',
							$results[0][$this->alias]) && $results[0][$this->alias]['user_font_size'] === null) {
				$results[0][$this->alias]['user_font_size'] = 1;
			}
			return $results;
		}

		public function beforeSave($options = array()) {
			parent::beforeSave($options);
			if (isset($this->data[$this->alias]['password'])) {
				if (!empty($this->data[$this->alias]['password'])) {
					$this->data[$this->alias]['password'] = $this->_hashPassword($this->data[$this->alias]['password']);
				}
			}

			if (isset($this->data[$this->alias]['user_category_custom'])) {
				$this->data[$this->alias]['user_category_custom'] =
						serialize($this->data[$this->alias]['user_category_custom']);
			}

			return true;
		}

		public function beforeValidate($options = array()) {
			parent::beforeValidate($options);

			if (isset($this->data[$this->alias]['user_forum_refresh_time'])
					&& empty($this->data[$this->alias]['user_forum_refresh_time'])) {
				$this->data[$this->alias]['user_forum_refresh_time'] = 0;
			}
		}

		public function validateCheckOldPassword($data) {
			$this->contain('UserOnline');
			$old_pw = $this->field('password');
			return $this->_checkPassword($data['password_old'], $old_pw);
		}

		public function validateConfirmPassword($data) {
			$valid = false;
			if (isset($this->data[$this->alias]['password_confirm'])
					&& $data['password'] == $this->data[$this->alias]['password_confirm']) {
				$valid = true;
			}
			return $valid;
		}

		/**
		 * Registers new user
		 *
		 * @param array $data
		 * @return bool true if user got registred false otherwise
		 */
		public function register($data) {
			$defaults = array(
					'registered'		 => date("Y-m-d H:i:s"),
					'user_type'			 => 'user',
					'user_view'			 => 'thread',
					'activate_code'	 => 0,
			);
			$data = array_merge($defaults, $data[$this->alias]);

			$this->create();
			$out = $this->save($data);

			return $out;
		}

		/**
		 * Garbage collection for registration
		 *
		 * Deletes all timed out and unactivated registrations
		 */
		protected function _registerGc() {
			Stopwatch::start('User::registerGc');
			$last_registerGc = Cache::read('Saito.Cache.registerGc');
			if(!$last_registerGc || $last_registerGc < time() - 21600) {
				$this->_registerGcHasRun = true;
				$this->deleteAll(array(
						'activate_code REGEXP "^[0-9][0-9]+$"',
						'registered <' => date('Y-m-d H:i:s', time() - 86400),
				), false);
				Cache::write('Saito.Cache.registerGc', time());
			}
			Stopwatch::stop('User::registerGc');
		}

		public function activate() {
			$success = $this->saveField('activate_code', 0);

			if ($success) :
				$this->contain();
				$user = $this->read();
				$this->getEventManager()->dispatch(
						new CakeEvent(
								'Model.User.afterActivate',
								$this,
								array('User' => $user['User'])
						)
				);
			endif;

			return $success;
		}

		/**
		 *
		 * @param int $id user-id
		 * @return bool|array false if not found, array otherwise
		 */
		public function getProfile($id) {
			$user = $this->find(
				'first',
				['contain' => false, 'conditions' => ['id' => $id]]
			);
			if ($user) {
				$user = $user[$this->alias];
			}
			return $user;
		}

		/**
		 * Set view categories preferences
		 *
		 * ## $category
		 *
		 * - 'all': set to all categories
		 * - array: (cat_id1 => true|1|'1', cat_id2 => true|1|'1')
		 * - int: set to single category_id
		 *
		 * @param string|int|array $category
		 * @throws InvalidArgumentException
		 */
		public function setCategory($category) {
			if ($category === 'all') {
				// set meta category 'all'
				$this->set('user_category_active', -1);
				$this->save();
			} elseif (is_array($category)) {
				// set custom set
				$available_cats = $this->Entry->Category->find('list');
				$categories = array_intersect_key($category, $available_cats);
				$new_cats = array();
				if (count($categories) === 0) {
					throw new InvalidArgumentException();
				} else {
					foreach ($categories as $cat => $v) {
						$new_cats[$cat] = ($v === true || $v === 1 || $v === '1');
					}
				}
				$this->set('user_category_active', 0);
				$this->set('user_category_custom', $new_cats);
				$this->save();
			} else {
				$category = (int)$category;
				if ($category > 0 && $this->Entry->Category->exists((int)$category)) {
					$this->set('user_category_active', $category);
					$this->save();
				} else {
					throw new InvalidArgumentException();
				}
			}
		}

		/**
		 * Checks if password is valid against all supported auth methods
		 *
		 * @param string $password
		 * @param string $hash
		 * @return boolean TRUE if password match FALSE otherwise
		 */
		protected function _checkPassword($password, $hash) {
			$supp_auths = [
					'BlowfishPasswordHasher',
					'Mlf2PasswordHasher',
					'MlfPasswordHasher'
			];
			$valid = false;
			foreach ($supp_auths as $auth) {
				$AuthClass = new $auth();
				// @: if hash is not valid hash blowfish Security::_crypt() triggers warnings
				if (@$AuthClass->check($password, $hash)) {
					$valid = true;
					break;
				}
			}
			return $valid;
		}

		/**
		 * Custom hash function used for authentication with Auth component
		 *
		 * @param string $password
		 * @return string hashed password
		 */
		protected function _hashPassword($password) {
			$auth = new BlowfishPasswordHasher();
			return $auth->hash($password);
		}

	}