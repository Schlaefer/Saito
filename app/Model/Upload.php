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
              'Upload.user_id' => $user_id),
              FALSE,
              // call beforeDelete FileUploader plugin callback to remove files from disk
              TRUE
          );
  }


}