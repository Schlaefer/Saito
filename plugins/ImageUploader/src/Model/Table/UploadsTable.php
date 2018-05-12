<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Model\Table;

use App\Lib\Model\Table\AppTable;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use claviska\SimpleImage;
use ImageUploader\Model\Entity\Upload;

class UploadsTable extends AppTable
{
    private const MAX_RESIZE = 800 * 1024;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', ['foreignKey' => 'user_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create')
            ->notBlank('name')
            ->notBlank('size')
            ->notBlank('type')
            ->notBlank('user_id')
            ->requirePresence(['name', 'size', 'type', 'user_id'], 'create');

        $maxUploadSize = (int)Configure::read('Saito.Settings.upload_max_img_size');
        $validator->add(
            'document',
            [
                'fileSize' => [
                    'rule' => ['fileSize', '<', $maxUploadSize . 'kB'],
                    'message' => __d(
                        'image_uploader',
                        'validation.error.fileSize',
                        $maxUploadSize
                    )
                ],
                'mimeType' => [
                    'rule' => ['mimeType', ['image/jpeg', 'image/png']],
                    'message' => __d(
                        'image_uploader',
                        'validation.error.mimeType',
                        'JPEG, PNG'
                    )
                ]
            ]
        );

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildRules(RulesChecker $rules)
    {
        // check max allowed number of uploads per user
        $nMax = (int)Configure::read('Saito.Settings.upload_max_number_of_uploads');
        $rules->add(
            function (Upload $entity, array $options) use ($nMax) {
                $count = $this->findByUserId($entity->get('user_id'))->count();

                return $count < $nMax;
            },
            'maxAllowedUploadsPerUser',
            [
                'errorField' => 'user_id',
                'message' => __d('image_uploader', 'validation.error.maxNumberOfItems', $nMax)
            ]
        );

        // check that user exists
        $rules->add($rules->existsIn('user_id', 'Users'));

        // check that same user can't have two items with the same name
        $rules->add(
            $rules->isUnique(
                ['name', 'user_id'],
                __d('image_uploader', 'validation.error.fileExists')
            )
        );

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave(Event $event, Upload $entity, \ArrayObject $options)
    {
        if (!$entity->isDirty('name') && !$entity->isDirty('document')) {
            return true;
        }
        try {
            $this->moveUpload($entity);
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeDelete(Event $event, Upload $entity, \ArrayObject $options)
    {
        if ($entity->get('file')->exists()) {
            return $entity->get('file')->delete();
        }

        return true;
    }

    /**
     * Puts uploaded file into upload folder
     *
     * @param Upload $entity upload
     * @return void
     */
    private function moveUpload(Upload $entity)
    {
        /** @var File $file */
        $file = $entity->get('file');
        try {
            $tmpFile = new File($entity->get('document')['tmp_name']);
            if (!$tmpFile->exists()) {
                throw new \RuntimeException('Uploaded file not found.');
            }

            if (!$tmpFile->copy($file->path)) {
                throw new \RuntimeException('Uploaded file could not be moved');
            }

            $this->fixOrientation($file);
            $size = $this->resize($file, self::MAX_RESIZE);
            $entity->set('size', $size);
        } catch (\Throwable $e) {
            if ($file->exists()) {
                $file->delete();
            }
            throw new \RuntimeException('Moving uploaded file failed.');
        }
    }

    /**
     * Fix image orientation according to image exif data
     *
     * @param File $file file
     * @return void
     */
    private function fixOrientation(File $file): void
    {
        $image = (new SimpleImage())
            ->fromFile($file->path)
            ->autoOrient()
            ->toFile($file->path);
    }

    /**
     * Resizes a file
     *
     * @param File $file file to resize
     * @param int $target size in bytes
     * @return int new file size
     */
    private function resize(File $file, int $target): int
    {
        $size = $file->size();
        if ($size < $target) {
            return $size;
        }

        $raw = $file->read();

        list($width, $height) = getimagesizefromstring($raw);
        $ratio = $size / $target;
        $ratio = sqrt($ratio);

        $newwidth = (int)($width / $ratio);
        $newheight = (int)($height / $ratio);
        $destination = imagecreatetruecolor($newwidth, $newheight);

        $source = imagecreatefromstring($raw);
        imagecopyresized($destination, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        $raw = $destination;

        $type = $file->mime();
        switch ($type) {
            case 'image/jpeg':
                imagejpeg($destination, $file->path);
                break;
            case 'image/png':
                imagepng($destination, $file->path);
                break;
            default:
                throw new \RuntimeException();
        }

        return $file->size();
    }
}
