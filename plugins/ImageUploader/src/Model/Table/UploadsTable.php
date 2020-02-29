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
use Cake\I18n\Number;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use ImageUploader\Lib\UploadStorage;
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

    /**
     * Handles persistent storage
     * @var \ImageUploader\Lib\UploadStorage
     */
    private UploadStorage $uploadStorage;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        $this->setEntityClass(Upload::class);

        $this->belongsTo('Users', ['foreignKey' => 'user_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmptyString('id', 'create')
            ->notBlank('name')
            ->notBlank('size')
            ->notBlank('type')
            ->notBlank('user_id')
            ->requirePresence(['name', 'size', 'type', 'user_id'], 'create');

        $validator->add(
            'tmp_name',
            [
                'file' => [
                    'rule' => [$this, 'validateFile'],
                ],
            ]
        );

        $validator->add(
            'title',
            [
                'maxLength' => [
                    'rule' => ['maxLength', self::FILENAME_MAXLENGTH],
                    'message' => __('vld.uploads.title.maxlength', self::FILENAME_MAXLENGTH),
                ],
            ]
        );

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        /** @var \ImageUploader\Lib\UploaderConfig $UploaderConfig */
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
                'message' => __d('image_uploader', 'validation.error.maxNumberOfItems', $nMax),
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
        if ($entity->isDirty('name') || $entity->isDirty('tmp_name')) {
            try {
                $this->getUploadStorage()->upload($entity);
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeDelete(Event $event, Upload $entity, \ArrayObject $options)
    {
        try {
            $this->getUploadStorage()->delete($entity);
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * Validate file by size
     *
     * @param mixed $check value
     * @param array $context context
     * @return string|bool
     */
    public function validateFile($check, array $context)
    {
        /** @var \ImageUploader\Lib\UploaderConfig $UploaderConfig */
        $UploaderConfig = Configure::read('Saito.Settings.uploader');

        $type = $context['data']['type'];

        /// Check file type
        if (!$UploaderConfig->hasType($type)) {
            return __d('image_uploader', 'validation.error.mimeType', $type);
        }

        /// Check file size
        $size = $UploaderConfig->getSize($type);
        if (!Validation::fileSize($check, '<', $size)) {
            return __d(
                'image_uploader',
                'validation.error.fileSize',
                Number::toReadableSize($size)
            );
        }

        return true;
    }

    /**
     * Getter for UploadHandler
     * @return \ImageUploader\Lib\UploadStorage UploadHandler
     */
    protected function getUploadStorage(): UploadStorage
    {
        if (empty($this->uploadStorage)) {
            /** @var \ImageUploader\Lib\UploaderConfig $UploaderConfig */
            $UploaderConfig = Configure::read('Saito.Settings.uploader');
            $this->uploadStorage = new UploadStorage($UploaderConfig->getStorageFilesystem());
        }

        return $this->uploadStorage;
    }
}
