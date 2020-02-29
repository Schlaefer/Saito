<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Controller;

use Api\Controller\ApiAppController;
use Api\Error\Exception\GenericApiException;
use Cake\Cache\Cache;
use Cake\Utility\Security;
use ImageUploader\Lib\MimeType;
use Psr\Http\Message\UploadedFileInterface;
use Saito\Exception\SaitoForbiddenException;
use Saito\User\Permission\ResourceAI;

/**
 * Upload Controller
 *
 * @property \Saito\User\CurrentUser\CurrentUserInterface $CurrentUser
 * @property \ImageUploader\Model\Table\UploadsTable $Uploads
 */
class UploadsController extends ApiAppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Users');
        $this->viewBuilder()->setHelpers(['ImageUploader.ImageUploader']);
    }

    /**
     * View uploads
     *
     * @return void
     */
    public function index()
    {
        $userId = (int)$this->getRequest()->getQuery('id');
        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($userId);
        $permission = $this->CurrentUser->permission(
            'saito.plugin.uploader.view',
            (new ResourceAI())->onRole($user->getRole())->onOwner($user->getId())
        );
        if (!$permission) {
            throw new SaitoForbiddenException(
                sprintf('Attempt to index uploads of "%s".', $userId),
                ['CurrentUser' => $this->CurrentUser]
            );
        }

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
        if (
            !($submitted instanceof UploadedFileInterface)
            || $submitted->getError() !== UPLOAD_ERR_OK
        ) {
            throw new GenericApiException(__d('image_uploader', 'add.failure'));
        }

        $userId = (int)$this->getRequest()->getData('userId');
        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($userId);
        $permission = $this->CurrentUser->permission(
            'saito.plugin.uploader.add',
            (new ResourceAI())->onRole($user->getRole())->onOwner($user->getId())
        );
        if (!$permission) {
            throw new SaitoForbiddenException(
                sprintf('Attempt to add uploads for "%s".', $userId),
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        $filename = $submitted->getClientFilename();
        $parts = explode('.', $filename);
        if (count($parts) < 2) {
            throw new GenericApiException(__d('image_uploader', 'add.failure.noext'));
        }
        $ext = array_pop($parts);
        $name = $this->CurrentUser->getId() .
                '_' .
                substr(Security::hash($filename, 'sha256'), 32) .
                '.' .
                $ext;
        $filepath = $submitted->getStream()->getMetadata('uri');
        $data = [
            'tmp_name' => $filepath,
            'name' => $name,
            'title' => $filename,
            'type' => MimeType::get($filepath, $name),
            'size' => filesize($filepath),
            'user_id' => $userId,
        ];
        $document = $this->Uploads->newEntity($data);

        $entity = $this->Uploads->save($document);
        if (!$entity) {
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
        /** @var \ImageUploader\Model\Entity\Upload $upload */
        $upload = $this->Uploads->get($imageId, ['contain' => ['Users']]);
        $permission = $this->CurrentUser->permission(
            'saito.plugin.uploader.delete',
            (new ResourceAI())->onRole($upload->user->getRole())->onOwner($upload->user->getId())
        );
        if (!$permission) {
            throw new SaitoForbiddenException(
                sprintf('Attempt to delete upload "%s".', $imageId),
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
