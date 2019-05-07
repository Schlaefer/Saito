<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Controller;

use Api\Controller\ApiAppController;
use Api\Error\Exception\GenericApiException;
use Cake\Cache\Cache;
use ImageUploader\Model\Table\UploadsTable;
use Saito\Exception\SaitoForbiddenException;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * Upload Controller
 *
 * @property CurrentUserInterface $CurrentUser
 * @property UploadsTable $Uploads
 */
class UploadsController extends ApiAppController
{
    public $helpers = ['ImageUploader.ImageUploader'];

    /**
     * View uploads
     *
     * @return void
     */
    public function index()
    {
        $userId = $this->CurrentUser->getId();
        $images = $this->Uploads->find()
            ->where(['user_id' => $userId])
            ->order(['id' => 'DESC'])
            ->all();
        $this->set('images', $images);
    }

    /**
     * Adds a new upload
     *
     * @return void
     */
    public function add()
    {
        $submitted = $this->request->getData('upload.0.file');
        if (!is_array($submitted)) {
            throw new GenericApiException('No uploaded image detected.');
        }
        $data = [
            'document' => $submitted,
            'name' => $this->CurrentUser->getId() . '_' . $submitted['name'],
            'size' => $submitted['size'],
            'type' => $submitted['type'],
            'user_id' => $this->CurrentUser->getId(),
        ];
        $document = $this->Uploads->newEntity($data);

        if (!$this->Uploads->save($document)) {
            $errors = $document->getErrors();
            $msg = $errors ? current(current($errors)) : null;
            throw new GenericApiException($msg);
        }

        $this->set('image', $document);
    }

    /**
     * Deletes an upload
     *
     * @param int $imageId the ID of the image to delete
     * @return void
     */
    public function delete($imageId)
    {
        $upload = $this->Uploads->get($imageId);
        if ($upload->get('user_id') !== $this->CurrentUser->getId()) {
            throw new SaitoForbiddenException(
                'Attempt to delete upload.',
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        if (!$this->Uploads->delete($upload)) {
            $msg = __d('image_uploader', 'delete.failure');
            throw new GenericApiException($msg);
        }

        Cache::delete((string)$imageId, 'uploadsThumbnails');

        $this->autoRender = false;
        $this->response = $this->response->withStatus(204);
    }
}
