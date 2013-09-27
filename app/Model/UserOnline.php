<?php

/**
 *  Class UserOnline
 */
class UserOnline extends AppModel {
	public  $name = 'UserOnline';
	public  $useTable = 'useronline';
	public  $primaryKey	= 'user_id';
 	public  $actsAs = array('Containable');

	public  $belongsTo = array (
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
	 * @param bool $loggedIn user is logged-in
	 */
	public function setOnline($id, $logged_in = null) {
//		Stopwatch::start('Model->UserOnline->setOnline()');

		if (empty($id)) {
			throw new InvalidArgumentException('Invalid Argument in setOnline()');
		}
		if ($logged_in === null) {
			throw new InvalidArgumentException('Invalid Argument $loggedIn in setOnline()');
		}

		$this->id = $this->_getShortendedId($id);

		//* setup data
		$data = [
			'UserOnline' => [
				'user_id'   => $this->id,
				'logged_in' => $logged_in
			]
		];

		$this->contain();
		$user = $this->read();

		if ($user) {
			// only hit database if timestamp is outdated
			if ($user['UserOnline']['time'] < (time() - $this->timeUntilOffline)) {
				$this->save($data);
			}
		} else {
			$this->id = null;
			$this->create();
			$this->save($data);
		}

		$this->_deleteOutdated();
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
		Stopwatch::start('UserOnline->getLoggedIn()');
		$loggedInUsers = $this->find(
						'all',
						array(
								'contain' 		=> 'User',
								'conditions' 	=> array ('UserOnline.logged_in ='  => 1),
								'fields'			=> 'User.id, User.username, User.user_type',
								'order'				=> 'User.username ASC',
						)
			);
		Stopwatch::stop('UserOnline->getLoggedIn()');
    return $loggedInUsers;
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