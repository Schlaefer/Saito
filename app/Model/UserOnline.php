<?php

/**
 *  Class UserOnline
 */
class UserOnline extends AppModel {
	var $name = 'UserOnline';
	var $useTable = 'useronline';
	var $primaryKey	= 'user_id';
 	var $actsAs = array('Containable');
//	public $cacheQueries = true;


	var $belongsTo = array (
		'User' => array (
				'className' => 'User',
				'foreignKey'	=> 'user_id',
		)
	);

	public $validation = array(
			'user_id'	=> array(),
	);

	/**
	 * Time in seconds until a user is considered offline
	 * 
	 * @var int
	 */
	public $timeUntilOffline  = 1200;

	public function beforeValidate($options = array()) {
		parent::beforeValidate($options);

		// @mlf use created/modified
		$this->data['UserOnline']['time'] = time();
	}

	/**
	 * Sets user `$id` online
	 * 
	 * The `$delete_id` is handy if a user logs in or out:
	 * We can remove his IP before setting the uid_<user_id> and vice versa.
	 *
	 * @param string $id `user_id` from table `User` or IP address
	 * @param string $delete_id remove this user before setting online `$id`
	 */
	public function setOnline($id, $loggedIn = NULL) {
//		self::$Timer->start('Model->UserOnline->setOnline()');

		if ( empty($id) ) {
			throw new InvalidArgumentException('Invalid Argument in setOnline()');	
		}
		if ( $loggedIn === NULL ) {
			throw new InvalidArgumentException('Invalid Argument $loggedIn in setOnline()');	
		}

		$this->id = $id; 

		//* setup data
		$data = array();
		$data['UserOnline']['user_id']	= $id;

		if ( $loggedIn == TRUE ) {
			$data['UserOnline']['logged_in']	= true;
		} else {
			$this->id = $data['UserOnline']['user_id'] = $this->_getShortendedId($id);
			$data['UserOnline']['logged_in']	= false;
			}

		$this->contain();
		$user = $this->read();
		
		if($user) {
			$this->id = $user['UserOnline']['id'];
			// only perform performance impacting save operation if user time stamp is actualy outdated
			if($user['UserOnline']['time'] < (time() - $this->timeUntilOffline)) {
				$this->save($data);
			}
		} else {
			$this->id = NULL;
			$this->create();
			$this->save($data);
		}

		// $this->log($this->find('all', array('contain'=>false)));
		$this->_deleteOutdated();

//		self::$Timer->stop('Model->UserOnline->setOnline()');
	}

	/**
	 * Removes user with `$id` from UserOnline
	 *
	 * @param string $id
	 */
	public function setOffline($id) {
		$this->id = $this->_getShortendedId($id);
		return $this->delete($id, false);
	}

	public function getLoggedIn() {
		 return $this->find(
						'all',
						array(
								'contain' 		=> 'User',
								'conditions' 	=> array ('UserOnline.logged_in ='  => 1),
								'fields'			=> 'User.id, User.username, User.user_type',
								'order'				=> 'User.username ASC',
						)
			);
		}

	/**
	 * deletes gone user
	 *
	 * Gone users are user who are not seen for $time_diff minutes.
	 *
	 * @param string $time_diff in minutes
	 */
	protected function _deleteOutdated($time_diff = NULL) {
		if( $time_diff === NULL ) $time_diff = $this->timeUntilOffline;
		$this->deleteAll( array ( 'time <' => time() - ( $time_diff )), false );
	}

	protected function _getShortendedId($id) {
		return substr($id, 0, 32);
		} 


}
?>