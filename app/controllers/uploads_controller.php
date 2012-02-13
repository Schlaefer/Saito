<?php

/**
 * Description of uploads_controller
 *
 * @author siezi
 */
class UploadsController extends AppController {
	public $name = 'Uploads';
	public $helpers = array( 'FileUpload.FileUpload', 'Number');

	/**
	 * Max # of uploads a user can do
	 *
	 * Set to '0' for unlimited uploads
	 *
	 * @var int
	 */
	public $maxUploadsPerUser = 10;

	/**
	 * Is the current user allowed to upload
	 * 
	 * @var bool
	 */
	public $isUploadAllowed = false;

	/**
	 * Uploads files
	 */
	public function add() {
		if (!$this->isUploadAllowed) {
				$this->Session->setFlash(sprintf(__('upload_max_number_of_uploads_failed'), $this->maxUploadsPerUser), 'flash/warning');
				$this->redirect(array('action' => 'index'));
		}

		if(!empty($this->request->data) && !empty($this->request->data['Upload'][0]['file']['tmp_name'])){
			 	$a['Upload'] = $this->request->data['Upload'][0];
			 	$a['Upload']['user_id'] = $this->Session->read('Auth.User.id');
			 	$a['Upload']['file']['name'] = Inflector::slug(
						$a['Upload']['user_id'] .'_'
						.pathinfo($a['Upload']['file']['name'],PATHINFO_FILENAME)
					)						
					.'.'.pathinfo($a['Upload']['file']['name'],PATHINFO_EXTENSION);
			 	$this->Upload->create();

				/**
				 * bubu, but the only way to set this setting before the Upload behavior's
				 * beforeValidate() is called
				 */
				$this->Upload->Behaviors->FileUpload->setUp(
					$this->Upload,
					array( 'maxFileSize' => (int) Configure::read('Saito.Settings.upload_max_img_size') * 1024 ));

				if($this->Upload->save($a)) {
					$this->Session->setFlash('Datei erfolgreich hochgeladen', 'flash/notice');
				}
				else {
					$errors = $this->Upload->invalidFields();
					$message = array();
					foreach( $errors as $field => $error) {
						$message[] =  __d('nondynamic', $field).": ". __d('nondynamic', $error);
					}
					$this->Session->setFlash('We are screwed, something went terribly wrong. File not uploaded.<br/>' . implode('<br/>',$message), 'flash/error');
				}
    }
		$this->redirect(array('action' => 'index'));
	} // end add()

	/**
	 * View uploads
	 */
	public function index() {
		$user_id = $this->Session->read('Auth.User.id');
		$images = $this->Upload->find(
						'all',
						array(
								'conditions' => array(
										'user_id' => $user_id
									),
								'order' => 'created desc'
							)
					);

		if (!$this->isUploadAllowed) {
				$this->Session->setFlash(sprintf(__('upload_max_number_of_uploads_failed'), $this->maxUploadsPerUser), 'flash/warning');
		}

		$this->set('images', $images);
		$this->render('/uploads/index', 'barebone');
	} //end index()

	/**
	 * Delete upload
	 */
	public function delete($id = null) {
		if($id == null) $this->redirect(array('action' => 'index'));

		$this->Upload->id = $id;
		$file = $this->Upload->read();
		if($file['Upload']['user_id'] == $this->Session->read('Auth.User.id')) {
			if($this->Upload->delete(null, false)) {
				$this->Session->setFlash('Datei gelöscht', 'flash/notice');
			}
			else {
				$this->Session->setFlash('We are screwed, something went terribly wrong. File not deleted.', 'flash/error');
			}
		}
		$this->redirect(array('action' => 'index'));
	} // end delete

	public function beforeFilter() {
		parent::beforeFilter();

		$this->maxUploadsPerUser = (int)Configure::read('Saito.Settings.upload_max_number_of_uploads');
		$this->_setUploadAllowedForUser($this->Session->read('Auth.User.id'));
	} // end beforeFilter()

	/**
	 * Checks if user `user_id` is allowed to upload files 
	 * 
	 * @param int $user_id
	 * @return bool
	 */
	protected function _setUploadAllowedForUser($user_id) {
		$this->isUploadAllowed = false;
		if(is_int($this->maxUploadsPerUser)) {
			if ($this->maxUploadsPerUser ===  0) {
				$this->isUploadAllowed = true;
			}
			else {
				$this->isUploadAllowed = !($this->_numberPicturesForUser($user_id) >= $this->maxUploadsPerUser);
			}
		}

		$this->set('isUploadAllowed', $this->isUploadAllowed);
		return $this->isUploadAllowed;
	} // end _isUploadAllowed()

	/**
	 * Returns the number of uploads a user `user_id` has made
	 * 
	 * @param int $user_id
	 * @return int number of files
	 */
	protected function _numberPicturesForUser($user_id) {
		return $this->Upload->find('count', array( 'conditions' => array( 'user_id' => $user_id)));
	} // end _numberPicturesForUser()

} // end class
?>