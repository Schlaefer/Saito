<?php

/**
 * @td verify that old pw is needed for changing pw(?) [See user.test.php both validatePw tests]
 */
class User extends AppModel {

	var $name = 'User';
	var $actsAs = array( 'Containable' );
	var $hasOne = array(
			'UserOnline' => array(
					'className' => 'UserOnline',
					'foreignKey' => 'user_id',
			),
	);
	var $hasMany = array(
			'Entry' => array(
					'className' => 'Entry',
					'foreignKey' => 'user_id',
			),
			'Upload' => array(
					'className' => 'Upload',
					'foreignKey' => 'user_id',
			)
	);

	var $validate = array(
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
							'rule' => array( '_validateConfirmPassword' ),
							'last' => 'true',
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
							'rule' => array( '_validateCheckOldPassword' ),
							'last' => 'true',
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
		$data[$this->alias]['last_refresh_tmp'] = date("Y-m-d H:i:s");

		if ( $lastRefresh ) {
			$data[$this->alias]['last_refresh'] = $lastRefresh;
		}

		$this->contain();
		if ( $this->save($data, TRUE, array( 'last_refresh_tmp', 'last_refresh' )) == FALSE ) {
			throw new Exception("Updating last user refresh failed.");
		}

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

	/**
	 * Custom hash function used for authentication with Auth component
	 * 
	 * @param array $data
	 * @return array
	 */
	public function hashPasswords($data) {
		if ( isset($data['User']['password']) ) {
			if ( !empty($data['User']['password']) ) {
				$data['User']['password'] = $this->_hash($data['User']['password']);
			}
		}
		return $data;

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
		$this->data = $this->hashPasswords($this->data);

		return true;

	}

	public function beforeValidate($options = array( )) {
		parent::beforeValidate($options);

		if ( isset($this->data[$this->alias]['user_forum_refresh_time']) && empty($this->data[$this->alias]['user_forum_refresh_time']) ) {
			$this->data[$this->alias]['user_forum_refresh_time'] = 0;
		}

	}

	protected function _validateCheckOldPassword($data) {
		$valid = false;
		$this->contain('UserOnline');
		$old_pw = $this->field('password');
		$new_pw = $this->_hash($data['password_old']);
		if ( $old_pw == $new_pw ) {
			$valid = true;
		}
		return $valid;

	}

	protected function _validateConfirmPassword($data) {
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

	/**
	 * parentNode for CakePHP ACL
	 *
	public function parentNode() {
		if ( !$this->id ) :
			return null;
		endif;

		$data = $this->data;

		if ( !isset($data[$this->alias]['group_id']) ) :
			// dont' use $this->read(), because you'll overwrite $this->data
			$data[$this->alias]['group_id'] = $this->field('group_id');
		endif;

		if ( !isset($data[$this->alias]['group_id']) ) :
			return null;
		else :
			// contrary to Cake doku use `foreign_key`, not `id` here
			return array( 'model' => 'Group', 'foreign_key' => $data[$this->alias]['group_id'] );
		endif;

	}
	*/


	/**
	 * Hashes strings according to app configuration with or without salt
	 *
	 * @param string $string
	 * @return string
	 */
	protected function _hash($string) {
		Security::setHash('md5');

		$salt = TRUE;
		if ( Configure::read('Saito.useSaltForUserPasswords') === FALSE ) :
			$salt = FALSE;
		endif;

		return Security::hash($string, null, $salt);

	}

}

// end class
?>