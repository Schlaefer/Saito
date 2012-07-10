<?php

  App::uses('AppModel', 'Model');
  App::uses('CakeEvent', 'Event');

  /**
   * Authentication methods
   */
  App::uses('BcryptAuthenticate', 'Controller/Component/Auth');
  App::uses('MlfAuthenticate', 'Controller/Component/Auth');
  App::uses('Mlf2Authenticate', 'Controller/Component/Auth');

  /**
 * @td verify that old pw is needed for changing pw(?) [See user.test.php both validatePw tests]
 */
class User extends AppModel {

	public $name = 'User';
	public $actsAs = array( 'Containable' );
	public $hasOne = array(
			'UserOnline' => array(
					'className' => 'UserOnline',
					'foreignKey' => 'user_id',
			),
	);
	public $hasMany = array(
			'Esnotifications' => array(
					'foreignKey' => 'user_id',
			),
			'Entry' => array(
					'className' => 'Entry',
					'foreignKey' => 'user_id',
			),
			'Upload' => array(
					'className' => 'Upload',
					'foreignKey' => 'user_id',
			),
	);

	public $validate = array(
			'username' => array(
					'isUnique' => array(
							'rule' => 'isUnique',
							'last' => 'true',
					),
					'notEmpty' => array(
							'rule' => 'notEmpty',
							'last' => 'true',
					),
			),
			'user_type' => array(
					'allowedChoice' => array(
							'rule' => array( 'inList', array( 'user', 'admin', 'mod' ) ),
					),
			),
			'password' => array(
					'notEmpty' => array(
							'rule' => 'notEmpty',
							'last' => 'true',
					),
					'pwConfirm' => array(
							'rule'    => array( 'validateConfirmPassword' ),
							'last'    => 'true',
              'message' => 'validation_error_pwConfirm',
					),
			),
			'password_confirm' => array(
			),
			'password_old' => array(
					'notEmpty' => array(
							'rule' => 'notEmpty',
							'last' => 'true',
					),
					'pwCheckOld' => array(
							'rule'    => array( 'validateCheckOldPassword' ),
							'last'    => 'true',
              'message' => 'validation_error_pwCheckOld',
					),
			),
			'user_email' => array(
					'isUnique' => array(
							'rule' => 'isUnique',
							'last' => 'true',
					),
					'isEmail' => array(
							'rule' => array( 'email', true ),
							'last' => 'true',
					)
			),
			'hide_email' => array(
					'rule' => array( 'boolean' ),
			),
			# @td we don't use this field yet
			'logins' => array(
					'rule' => 'numeric',
			),
			'registered' => array(
			),
			'user_view' => array(
					'allowedChoice' => array(
							'rule' => array( 'inList', array( 'thread', 'mix', 'board' ) ),
					),
			),
			'new_postin_notify' => array(
					'rule' => array( 'boolean' ),
			),
			'personal_messages' => array(
					'rule' => array( 'boolean' ),
			),
			'time_difference' => array(
			),
			# User durch admin/mod gesperrt?
			'user_lock' => array(
					'rule' => array( 'boolean' ),
			),
			/*
			 * password forgotten code
			 *
			 * store temporary md5 code after password is send
			 */
			'pwf_code' => array(
			),
			'activate_code' => array(
			),
			'user_font_size' => array(
					'rule' => 'numeric',
			),
			'user_signatures_hide' => array(
					'rule' => array( 'boolean' ),
			),
			'user_signature_images_hide' => array(
					'rule' => array( 'boolean' ),
			),
			'user_forum_refresh_time' => array(
					'numeric' => array(
							'rule' => 'numeric',
					),
					'greaterNull' => array(
							'rule' => array( 'comparison', '>=', 0 ),
					),
					'maxLength' => array(
							'rule' => array( 'maxLength', 3 ),
					),
			),
			'user_forum_hr_ruler' => array(
					'rule' => array( 'boolean' ),
			),
			'user_automaticaly_mark_as_read' => array(
					'rule' => array( 'boolean' ),
			),
			'user_sort_last_answer' => array(
					'rule' => array( 'boolean' ),
			),
			'user_show_own_signature' => array(
					'rule' => array( 'boolean' ),
			),
			'user_color_new_postings' => array(
					'rule' => '/^#?[a-f0-9]{0,6}$/i',
			),
			'user_color_old_postings' => array(
					'rule' => '/^#?[a-f0-9]{0,6}$/i',
					'message' => '*',
			),
			'user_color_actual_posting' => array(
					'rule' => '/^#?[a-f0-9]{0,6}$/i',
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

	public function setLastRefresh($lastRefresh = NULL) {
		Stopwatch::start('Users->setLastRefresh()');
		$data[$this->alias]['last_refresh_tmp'] = date("Y-m-d H:i:s");

		if ( $lastRefresh ) {
			$data[$this->alias]['last_refresh'] = $lastRefresh;
		}

		$this->contain();
		if ( $this->save($data, TRUE, array( 'last_refresh_tmp', 'last_refresh' )) == FALSE ) {
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
			$count = $this->Entry->find('count', array(
							'contain' => false,
							'conditions' => array( 'Entry.user_id' => $this->id ),
							)
			);
		}
		return $count;

	}

	public function incrementLogins($amount = 1) {
		$data = array( );
		$data[$this->alias] = array(
				'logins' => $this->field('logins') + $amount,
				'last_login' => date('Y-m-d H:i:s'),
		);
		$this->contain();
		if ( $this->save($data, TRUE, array( 'logins', 'last_login')) == FALSE ) {
			throw new Exception("Increment logins failed.");
		}

	}

  public function deleteAllExceptEntries($id) {
    if ($id == 1) return FALSE;

    $success = TRUE;
    $success = $success && $this->Upload->deleteAllFromUser($id);
    $success = $success && $this->Entry->anonymizeEntriesFromUser($id);
    $success = $success && $this->UserOnline->deleteAll(
        array('user_id' => $id), FALSE);
    $success = $success && $this->delete($id, FALSE);
    return $success;
  }

  public function autoUpdatePassword($password) {
    $this->contain();
    $data = $this->read();
    $oldPassword = $data['User']['password'];
    if ( strpos($oldPassword, BcryptAuthenticate::$hashIdentifier) !== 0 ):
      $this->saveField('password', $password);
    endif;
  }

	public function afterFind($results, $primary = false) {
		$results = parent::afterFind($results, $primary);

		if ( isset($results[0][$this->alias]) && array_key_exists('user_color_new_postings', $results[0][$this->alias]) ) {
			//* @td refactor this shit
			if ( empty($results[0][$this->alias]['user_color_new_postings']) ) {
				$results[0][$this->alias]['user_color_new_postings'] = '#';
				$results[0][$this->alias]['user_color_old_postings'] = '#';
				$results[0][$this->alias]['user_color_actual_posting'] = '#';
			}
		}

		# @td font-size
		if ( isset($results[0][$this->alias]) && array_key_exists('user_font_size', $results[0][$this->alias]) && $results[0][$this->alias]['user_font_size'] === NULL ) {
			$results[0][$this->alias]['user_font_size'] = 1;
		}
		return $results;

	}

	public function beforeSave($options = array( )) {
		parent::beforeSave($options);
		if ( isset($this->data['User']['password']) ) {
			if ( !empty($this->data['User']['password']) ) {
        $this->data['User']['password'] = $this->_hashPassword($this->data['User']['password']);
			}
		}

		return true;

	}

	public function beforeValidate($options = array( )) {
		parent::beforeValidate($options);

		if ( isset($this->data[$this->alias]['user_forum_refresh_time']) && empty($this->data[$this->alias]['user_forum_refresh_time']) ) {
			$this->data[$this->alias]['user_forum_refresh_time'] = 0;
		}

	}

	public function validateCheckOldPassword($data) {
		$valid = false;
		$this->contain('UserOnline');
		$old_pw = $this->field('password');
		if ( $this->_checkPassword($data['password_old'], $old_pw) ) :
			$valid = true;
    elseif ( $old_pw === MlfAuthenticate::hash($data['password_old']) ) :
			$valid = true;
    elseif ( Mlf2Authenticate::checkPassword($data['password_old'], $old_pw) ) :
			$valid = true;
    endif;
		return $valid;

	}

	public function validateConfirmPassword($data) {
		$valid = false;

		if ( isset($this->data[$this->alias]['password_confirm']) && $data['password'] == $this->data[$this->alias]['password_confirm'] ) {
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
				'registered' => date("Y-m-d H:i:s"),
				'user_type' => 'user',
				'user_view' => 'thread',
		);
		$data = array_merge($defaults, $data[$this->alias]);

		$this->create();
		$out = $this->save($data);

		return $out;

	}

  public function activate() {
    $success = $this->saveField('activate_code', '');

    if ( $success ) :
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
   * @param string $password
   * @param string $hash
   * @return boolean TRUE if password match FALSE otherwise
   */
  public function _checkPassword($password, $hash) {
    return BcryptAuthenticate::checkPassword($password, $hash);
  }

	/**
	 * Custom hash function used for authentication with Auth component
	 *
	 * @param string $password
	 * @return string hashed password
	 */
	protected function _hashPassword($password) {
    return BcryptAuthenticate::hash($password);
	}

}

?>