<?php

	App::uses('AppController', 'Controller');

	class UploadsController extends AppController {

		public $name = 'Uploads';

		public $helpers = array('FileUpload.FileUpload', 'Number');

/**
 * Max # of uploads a user can do
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
 * Uploads new files
 *
 * @return string
 * @throws MethodNotAllowedException
 */
		public function add() {
			$this->autoRender = false;

			if ($this->request->is('post') === false) {
				throw new MethodNotAllowedException();
			}

			if ($this->isUploadAllowed === false) {
				$message = sprintf(
					__('upload_max_number_of_uploads_failed'),
					$this->maxUploadsPerUser
				);
				$this->JsData->addAppJsMessage($message, 'error');
			} else {
				if (!empty($this->request->data) && !empty($this->request->data['Upload'][0]['file']['tmp_name'])) {
					$a['Upload'] = $this->request->data['Upload'][0];
					$a['Upload']['user_id'] = $this->Session->read('Auth.User.id');
					$a['Upload']['file']['name'] = Inflector::slug(
								$a['Upload']['user_id'] . '_' .
								pathinfo($a['Upload']['file']['name'], PATHINFO_FILENAME)
							) .
							'.' . pathinfo(
								$a['Upload']['file']['name'],
								PATHINFO_EXTENSION
							);
					$this->Upload->create();

					// @bogus, but the only way to set this before the Upload behavior's
					// beforeValidate() is called
					$this->Upload->Behaviors->FileUpload->setUp(
						$this->Upload,
						array(
							'maxFileSize' => (int)Configure::read(
								'Saito.Settings.upload_max_img_size'
							) * 1024
						)
					);

					if (!$this->Upload->save($a)) {
						$errors = $this->Upload->validationErrors;
						$message = array();
						foreach ($errors as $error) {
							$message[] = __d('nondynamic', $error);
						}
						$this->JsData->addAppJsMessage($message, 'error');
					}
				}
			}
			return json_encode($this->JsData->getAppJsMessages());
		}

/**
 * View uploads
 *
 * @throws BadRequestException
 */
		public function index() {
			if ($this->request->is('ajax') === false) {
				throw new BadRequestException();
			}

			$userId = $this->CurrentUser->getId();
			$images = $this->Upload->find(
				'all',
				array(
					'conditions' => array(
						'user_id' => $userId
					),
					'order' => 'created ASC'
				)
			);
			$this->set('images', $images);
		}

/**
 * Delete upload
 *
 * @param null $id
 *
 * @return string
 * @throws BadRequestException
 * @throws ForbiddenException
 */
		public function delete($id = null) {
			if ($this->request->is('ajax') === false || $id === null) {
				throw new BadRequestException();
			}
			$this->autoRender = false;

			$this->Upload->id = (int)$id;
			$file = $this->Upload->read();
			if ($file &&
					(int)$file['Upload']['user_id'] === $this->CurrentUser->getId()
			) {
				if (!$this->Upload->delete(null, false)) {
					$this->JsData->addAppJsMessage(
						'We are screwed, something went terribly wrong. File not deleted.',
						'error'
					);
				}
			} else {
				throw new ForbiddenException();
			}
			return json_encode($this->JsData->getAppJsMessages());
		}

/**
 * @return CakeResponse|void
 * @throws ForbiddenException
 */
		public function beforeFilter() {
			$this->Security->unlockedActions = ['add', 'delete'];
			parent::beforeFilter();

			if ($this->CurrentUser->isLoggedIn() === false) {
				throw new ForbiddenException();
			}
			$this->_init();
		}

		protected function _init() {
			$this->maxUploadsPerUser = (int)Configure::read(
				'Saito.Settings.upload_max_number_of_uploads'
			);
			$countCurrent = $this->Upload->countUser($this->CurrentUser->getId());
			$this->isUploadAllowed = $countCurrent < $this->maxUploadsPerUser;
		}

	}
