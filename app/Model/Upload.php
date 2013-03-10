<?php

/**
 * Description of upload
 *
 * @author siezi
 */
class Upload extends AppModel {

	public $name = 'Upload';

	public $recursive = -1;

	public $actsAs = array('FileUpload.FileUpload');

	public $belongsTo = array('User');

	public function deleteAllFromUser($user_id) {
		return $this->deleteAll(
			array(
				'Upload.user_id' => $user_id
			),
			false,
			// call beforeDelete FileUploader plugin callback to remove files from disk
			true
		);
	}

	/**
	 * Returns the number of uploads a user `user_id` has made
	 *
	 * @param int $user_id
	 * @return int number of files
	 */
	public function countUser($user_id) {
		$number = $this->find(
			'count',
			array(
				'conditions' => array('user_id' => $user_id)
			)
		);
		return (int)$number;
	}


}