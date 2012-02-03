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

}
?>