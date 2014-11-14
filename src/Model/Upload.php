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

	public function deleteAllFromUser($userId) {
		return $this->deleteAll(
			array(
				'Upload.user_id' => $userId
			),
			false,
			// call beforeDelete FileUploader plugin callback to remove files from disk
			true
		);
	}

/**
 * Returns the number of uploads a user `user_id` has made
 *
 * @param $userId
 *
 * @return int
 */
	public function countUser($userId) {
		$number = $this->find(
			'count',
			array(
				'conditions' => array('user_id' => $userId)
			)
		);
		return (int)$number;
	}

}