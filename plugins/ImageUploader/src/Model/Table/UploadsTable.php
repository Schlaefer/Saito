<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Model\Table;

use App\Lib\Model\Table\AppTable;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\I18n\Number;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use claviska\SimpleImage;
use ImageUploader\Model\Entity\Upload;

/**
 * Uploads
 *
 * Indeces:
 * - user_id, title - Combined used for uniqueness test. User_id for user's
 *   upload overview page.
 */
class UploadsTable extends AppTable
{
    /**
     * Max filename length.
     *
     * Constrained to 191 due to InnoDB index max-length on MySQL 5.6.
     */
    public const FILENAME_MAXLENGTH = 191;

    private const MAX_RESIZE = 800 * 1024;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        $this->setEntityClass(Upload::class);

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

        /** @var \ImageUploader\Lib\UploaderConfig */
        $UploaderConfig = Configure::read('Saito.Settings.uploader');

        $validator->add(
            'document',
            [
                'mimeType' => [
                    'rule' => [
                        'mimeType',
                        $UploaderConfig->getAllTypes(),
                    ],
                    'message' => __d(
                        'image_uploader',
                        'validation.error.mimeType'
                    )
                ],
                'fileSize' => [
                    'rule' => [$this, 'validateFileSize'],
                ],
            ]
        );

        $validator->add(
            'title',
            [
                'maxLength' => [
                    'rule' => ['maxLength', self::FILENAME_MAXLENGTH],
                    'message' => __('vld.uploads.title.maxlength', self::FILENAME_MAXLENGTH)
                ],
            ]
        );

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildRules(RulesChecker $rules)
    {
        /** @var \ImageUploader\Lib\UploaderConfig */
        $UploaderConfig = Configure::read('Saito.Settings.uploader');
        $nMax = $UploaderConfig->getMaxNumberOfUploadsPerUser();
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
                // Don't use a identifier like "name" which changes (jpg->png).
                ['title', 'user_id'],
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
    private function moveUpload(Upload $entity): void
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

            $mime = $file->info()['mime'];
            switch ($mime) {
                case 'image/png':
                    $file = $this->convertToJpeg($file);
                    // fall through: png is further processed as jpeg
                    // no break
                case 'image/jpeg':
                    $this->fixOrientation($file);
                    $this->resize($file, self::MAX_RESIZE);
                    $entity->set('size', $file->size());
                    break;
                default:
            }

            $entity->set('name', $file->name);
            $entity->set('type', $file->mime());
        } catch (\Throwable $e) {
            if ($file->exists()) {
                $file->delete();
            }
            throw new \RuntimeException('Moving uploaded file failed.');
        }
    }

    /**
     * Convert image file to jpeg
     *
     * @param File $file the non-jpeg image file handler
     * @return File handler to jpeg file
     */
    private function convertToJpeg(File $file): File
    {
        $jpeg = new File($file->folder()->path . DS . $file->name() . '.jpg');

        try {
            (new SimpleImage())
                ->fromFile($file->path)
                ->toFile($jpeg->path, 'image/jpeg', 75);
        } catch (\Throwable $e) {
            if ($jpeg->exists()) {
                $jpeg->delete();
            }
            throw new \RuntimeException('Converting file to jpeg failed.');
        } finally {
            $file->delete();
        }

        return $jpeg;
    }

    /**
     * Fix image orientation according to image exif data
     *
     * @param File $file file
     * @return File handle to fixed file
     */
    private function fixOrientation(File $file): File
    {
        $new = new File($file->path);
        (new SimpleImage())
            ->fromFile($file->path)
            ->autoOrient()
            ->toFile($new->path, null, 75);

        return $new;
    }

    /**
     * Resizes a file
     *
     * @param File $file file to resize
     * @param int $target size in bytes
     * @return void
     */
    private function resize(File $file, int $target): void
    {
        $size = $file->size();
        if ($size < $target) {
            return;
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
    }

    /**
     * Validate file by size
     *
     * @param mixed $check value
     * @param array $context context
     * @return string|bool
     */
    public function validateFileSize($check, array $context)
    {
        /** @var \ImageUploader\Lib\UploaderConfig */
        $UploaderConfig = Configure::read('Saito.Settings.uploader');
        $type = $check['type'];

        if (!$UploaderConfig->hasType($type)) {
            return __d(
                'image_uploader',
                'validation.error.mimeType',
                $type
            );
        }

        $size = $UploaderConfig->getSize($check['type']);
        $result = Validation::fileSize($check, '<', $size);

        if ($result !== true) {
            return __d(
                'image_uploader',
                'validation.error.fileSize',
                Number::toReadableSize($size)
            );
        }

        return true;
    }
}
