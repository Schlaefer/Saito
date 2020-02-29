<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Lib;

use Cake\Core\Configure;
use ImageUploader\Model\Entity\Upload;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;

class UploadStorage
{
    /**
     * Filesystem where to store the uploads
     * @var \League\Flysystem\FilesystemInterface
     */
    protected FilesystemInterface $targetFilesystem;

    /**
     * Constructor
     * @param \League\Flysystem\FilesystemInterface $targetFilesystem Target file system
     * @return void
     */
    public function __construct(FilesystemInterface $targetFilesystem)
    {
        $this->targetFilesystem = $targetFilesystem;
    }

    /**
     * Process the upload: type conversion, copying to target filesystem, …
     * @param \ImageUploader\Model\Entity\Upload $entity Upload entity
     * @return void
     */
    public function upload(Upload $entity): void
    {
        $file = new File($entity->get('tmp_name'));
        if (!$file->exists()) {
            throw new \RuntimeException('Uploaded file not found.');
        }

        $mime = $file->getMime();
        switch ($mime) {
            case 'image/png':
                ImageProcessor::convertToJpeg($file->getPath());
                $this->changeExtention($entity, 'jpg');
                // fall through: png is further processed as jpeg
                // no break
            case 'image/jpeg':
                ImageProcessor::fixOrientation($file->getPath());
                /** @var \ImageUploader\Lib\UploaderConfig $UploaderConfig */
                $UploaderConfig = Configure::read('Saito.Settings.uploader');
                ImageProcessor::resize($file, $UploaderConfig->getMaxResize());
                break;
            default:
        }

        $this->updateMeta($file, $entity);
        $this->store($file, $entity);
    }

    /**
     * Move Upload to target file system
     * @param \ImageUploader\Lib\File $file file
     * @param \ImageUploader\Model\Entity\Upload $entity entity
     * @return void
     */
    protected function store(File $file, Upload $entity): void
    {
        try {
            $manager = new MountManager([
                'source' => $file->getFs(),
                'target' => $this->targetFilesystem,
            ]);
            $isCopied = $manager->copy(
                'source://' . $file->getBasename(),
                'target://' . $entity->get('name')
            );
            if (!$isCopied) {
                throw new \RuntimeException('Uploaded file could not be moved');
            }
        } catch (\Throwable $e) {
            if ($this->targetFilesystem->has($entity->get('name'))) {
                $this->targetFilesystem->delete($entity->get('name'));
            }
            throw $e;
        }
    }

    /**
     * Delete a file from storage
     * @param \ImageUploader\Model\Entity\Upload $entity UploadEntity
     * @return void
     */
    public function delete(Upload $entity): void
    {
        $file = new File($entity->get('name'), $this->targetFilesystem);
        if ($file->exists()) {
            $file->delete();
        }
    }

    /**
     * Change file extension on target file name
     * @param \ImageUploader\Model\Entity\Upload $entity Entity with target file name
     * @param string $ext New file extension
     * @return void
     */
    protected function changeExtention(Upload $entity, string $ext)
    {
        $newName = pathinfo($entity->get('name'))['filename'] . '.' . $ext;
        $entity->set('name', $newName);
    }

    /**
     * Update entity metadata from storage
     * @param \ImageUploader\Lib\File $file file
     * @param \ImageUploader\Model\Entity\Upload $entity Entity with target file name
     * @return void
     */
    protected function updateMeta(File $file, Upload $entity): void
    {
        $entity->set('type', $file->getMime());
        $entity->set('size', $file->getSize());
    }
}
