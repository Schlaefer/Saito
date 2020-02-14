<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppTable;
use App\Lib\Model\Table\FieldFilter;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\PasswordHasher\PasswordHasherFactory;
use Authentication\PasswordHasher\PasswordHasherInterface;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use DateTimeInterface;
use Saito\App\Registry;
use Saito\User\Upload\AvatarFilenameListener;
use Stopwatch\Lib\Stopwatch;

/**
 * Users table
 *
 * @property \App\Model\Table\EntriesTable $Entries
 * @property \App\Model\Table\UserBlocksTable $UserBlocks
 * @property \App\Model\Table\UserIgnoresTable $UserIgnores
 * @property \App\Model\Table\UserOnlineTable $UserOnline
 */
class UsersTable extends AppTable
{
    /**
     * Max lenght for username.
     *
     * Constrained to 191 due to InnoDB index max-length on MySQL 5.6.
     */
    public const USERNAME_MAXLENGTH = 191;

    /**
     * {@inheritDoc}
     */
    protected $_defaultConfig = [
        'user_name_disallowed_chars' => ['\'', ';', '&', '<', '>'],
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        $this->addBehavior(
            'Cron.Cron',
            [
                'registerGc' => [
                    'id' => 'User.registerGc',
                    'due' => '+1 day',
                ],
                'userBlockGc' => [
                    'id' => 'User.userBlockGc',
                    'due' => '+15 minutes',
                ],
            ]
        );

        $avatarRootDir = Configure::read('Saito.Settings.uploadDirectory');
        $this->addBehavior(
            'Proffer.Proffer',
            [
                'avatar' => [ // The name of your upload field (filename)
                    'root' => $avatarRootDir,
                    'dir' => 'avatar_dir', // field for upload directory
                    'thumbnailSizes' => [
                        'square' => ['w' => 100, 'h' => 100],
                    ],
                    // Options are Imagick, Gd or Gmagick
                    'thumbnailMethod' => 'Gd',
                ],
            ]
        );
        $this->getEventManager()->on(new AvatarFilenameListener($avatarRootDir));

        $this->hasOne(
            'UserOnline',
            ['dependent' => true, 'foreignKey' => 'user_id']
        );

        $this->hasMany(
            'Bookmarks',
            ['foreignKey' => 'user_id', 'dependent' => true]
        );
        $this->hasMany('Drafts', ['dependent' => true]);
        $this->hasMany('UserIgnores', ['foreignKey' => 'user_id']);
        $this->hasMany(
            'Entries',
            [
                'foreignKey' => 'user_id',
                'conditions' => ['Entries.user_id' => 'Users.id'],
            ]
        );
        $this->hasMany(
            'ImageUploader.Uploads',
            ['dependent' => true, 'foreignKey' => 'user_id']
        );
        $this->hasMany(
            'UserReads',
            ['foreignKey' => 'user_id', 'dependent' => true]
        );
        $this->hasMany(
            'UserBlocks',
            [
                'foreignKey' => 'user_id',
                'dependent' => true,
                'sort' => [
                    'UserBlocks.ended IS NULL DESC',
                    'UserBlocks.ended' => 'DESC',
                    'UserBlocks.id' => 'DESC',
                ],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator->setProvider(
            'saito',
            'Saito\Validation\SaitoValidationProvider'
        );

        $validator
            ->setProvider(
                'proffer',
                'Proffer\Model\Validation\ProfferRules'
            )
            ->allowEmptyString('avatar_dir')
            ->allowEmptyString('avatar')
            ->add(
                'avatar',
                'avatar-extension',
                [
                    'rule' => ['extension', ['jpg', 'jpeg', 'png']],
                    'message' => __('user.avatar.error.extension', ['jpg, jpeg, png']),
                ]
            )
            ->add(
                'avatar',
                'avatar-size',
                [
                    'rule' => ['fileSize', Validation::COMPARE_LESS, '3MB'],
                    'message' => __('user.avatar.error.size', ['3']),
                ]
            )
            ->add(
                'avatar',
                'avatar-mime',
                [
                    'rule' => ['mimetype', ['image/jpeg', 'image/png']],
                    'message' => __('user.avatar.error.mime'),
                ]
            )
            ->add(
                'avatar',
                'avatar-dimension',
                [
                    'rule' => [
                        'dimensions',
                        [
                            'min' => ['w' => 100, 'h' => 100],
                            'max' => ['w' => 1500, 'h' => 1500],
                        ],
                    ],
                    'message' => __(
                        'user.avatar.error.dimension',
                        ['100x100', '1500x1500']
                    ),
                    'provider' => 'proffer',
                ]
            );

        $validator
            ->notEmptyString('password')
            ->add(
                'password',
                [
                    'pwConfirm' => [
                        'rule' => [$this, 'validateConfirmPassword'],
                        'last' => true,
                        'message' => __('error_password_confirm'),
                    ],
                ]
            );

        $validator
            ->notEmptyString('password_old')
            ->add(
                'password_old',
                [
                    'pwCheckOld' => [
                        'rule' => [$this, 'validateCheckOldPassword'],
                        'last' => true,
                        'message' => 'validation_error_pwCheckOld',
                    ],
                ]
            );

        $validator
            ->notEmptyString('username', __('error_no_name'))
            ->add(
                'username',
                [
                    'isUnique' => [
                        'rule' => 'validateIsUniqueCiString',
                        'provider' => 'saito',
                        'last' => true,
                        'message' => __('error_name_reserved'),
                    ],
                    'isUsernameEqual' => [
                        'on' => 'create',
                        'last' => true,
                        'rule' => [$this, 'validateUsernameEqual'],
                    ],
                    'hasAllowedChars' => [
                        'rule' => [$this, 'validateHasAllowedChars'],
                        'message' => __(
                            'model.user.validate.username.hasAllowedChars'
                        ),
                    ],
                    'isNotEmoji' => [
                        'rule' => 'utf8',
                        'message' => __(
                            'model.user.validate.username.hasAllowedChars'
                        ),
                    ],
                    'maxLength' => [
                        'last' => true,
                        'message' => __('vld.users.username.maxlength', self::USERNAME_MAXLENGTH),
                        'rule' => ['maxLength', self::USERNAME_MAXLENGTH],
                    ],
                ]
            );

        $validator
            ->notEmptyString('user_email')
            ->add(
                'user_email',
                [
                    'isUnique' => [
                        'rule' => 'validateUnique',
                        'provider' => 'table',
                        'last' => true,
                        'message' => __('error_email_reserved'),
                    ],
                    'isEmail' => [
                        'rule' => ['email', true],
                        'last' => true,
                        'message' => __('error_email_wrong'),
                    ],
                ]
            );

        $validator->add(
            'user_forum_refresh_time',
            [
                'numeric' => ['rule' => 'numeric'],
                'greaterNull' => ['rule' => ['comparison', '>=', 0]],
                'maxLength' => ['rule' => ['maxLength', 3]],
            ]
        );

        $validator->add(
            'user_type',
            [
                'allowedType' => [
                    'rule' => [$this, 'validateUserRoleExists'],
                ],
            ]
        );

        $validator->notEmptyDateTime('registered');

        $validator->add(
            'logins',
            ['numeric' => ['rule' => ['numeric']]]
        );

        $validator->add(
            'personal_messages',
            ['bool' => ['rule' => ['boolean']]]
        );

        $validator->add(
            'user_lock',
            ['bool' => ['rule' => ['boolean']]]
        );

        $validator
            ->notEmptyString('activate_code')
            ->add(
                'activate_code',
                [
                    'numeric' => ['rule' => ['numeric']],
                    'between' => ['rule' => ['range', 0, 9999999]],
                ]
            );

        $validator->add(
            'user_signatures_hide',
            ['bool' => ['rule' => ['boolean']]]
        );

        $validator->add(
            'user_signature_images_hide',
            ['bool' => ['rule' => ['boolean']]]
        );

        $validator->add(
            'user_automaticaly_mark_as_read',
            ['bool' => ['rule' => ['boolean']]]
        );

        $validator->add(
            'user_sort_last_answer',
            ['bool' => ['rule' => ['boolean']]]
        );

        $validator
            ->allowEmptyString('user_color_new_postings')
            ->add(
                'user_color_new_postings',
                [
                    'hexformat' => [
                        'rule' => ['custom', '/^#?[a-f0-9]{0,6}$/i'],
                    ],
                ]
            );
        $validator
            ->allowEmptyString('user_color_old_postings')
            ->add(
                'user_color_old_postings',
                [
                    'hexformat' => [
                        'rule' => ['custom', '/^#?[a-f0-9]{0,6}$/i'],
                    ],
                ]
            );
        $validator
            ->allowEmptyString('user_color_actual_posting')
            ->add(
                'user_color_actual_posting',
                [
                    'hexformat' => [
                        'rule' => ['custom', '/^#?[a-f0-9]{0,6}$/i'],
                    ],
                ]
            );

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
    {
        $schema->setColumnType('avatar', 'proffer.file');
        $schema->setColumnType('user_category_custom', 'serialize');

        return $schema;
    }

    /**
     * set last refresh
     *
     * @param int $userId user-ID
     * @param \DateTimeInterface|null $lastRefresh last refresh
     * @return void
     */
    public function setLastRefresh(int $userId, ?DateTimeInterface $lastRefresh = null)
    {
        Stopwatch::start('Users->setLastRefresh()');
        $data['last_refresh_tmp'] = bDate();

        if ($lastRefresh) {
            $data['last_refresh'] = $lastRefresh;
        }

        $this->query()
            ->update()
            ->set($data)
            ->where(['id' => $userId])
            ->execute();

        Stopwatch::end('Users->setLastRefresh()');
    }

    /**
     * Increment logins
     *
     * @param \Cake\ORM\Entity $user user
     * @param int $amount amount
     * @return void
     * @throws \Exception
     */
    public function incrementLogins(Entity $user, $amount = 1)
    {
        $data = [
            'logins' => $user->get('logins') + $amount,
            'last_login' => bDate(),
        ];
        $this->patchEntity($user, $data);
        if (!$this->save($user)) {
            throw new \Exception('Increment logins failed.');
        }
    }

    /**
     * get userlist
     *
     * @return array
     */
    public function userlist()
    {
        return $this->find(
            'list',
            ['keyField' => 'id', 'valueField' => 'username']
        )->toArray();
    }

    /**
     * Removes a user and all his data execpt for his entries
     *
     * @param int $userId user-ID
     * @return bool
     */
    public function deleteAllExceptEntries(int $userId)
    {
        $user = $this->get($userId);
        if (empty($user)) {
            return false;
        }

        try {
            $this->Entries->anonymizeEntriesFromUser($userId);
            $this->UserIgnores->deleteUser($userId);
            $this->delete($user);
        } catch (\Exception $e) {
            return false;
        }
        $this->dispatchDbEvent('Cmd.Cache.clear', ['cache' => 'Thread']);

        return true;
    }

    /**
     * Updates the hashed password if hash-algo is out-of-date
     *
     * @param int $userId user-ID
     * @param string $password password
     * @return void
     */
    public function autoUpdatePassword(int $userId, string $password): void
    {
        $user = $this->get($userId, ['fields' => ['id', 'password']]);
        $oldPassword = $user->get('password');
        $needsRehash = $this->getPasswordHasher()->needsRehash($oldPassword);
        if ($needsRehash) {
            $user->set('password', $password);
            $this->save($user);
        }
    }

    /**
     * Post processing when updating a username.
     *
     * @param \Cake\ORM\Entity $entity The updated entity.
     * @return void
     */
    protected function updateUsername(Entity $entity)
    {
        // Using associating with $this->Entries->updateAll() not working in
        // Cake 3.8.
        $Entries = TableRegistry::getTableLocator()->get('Entries');
        $Entries->updateAll(
            ['name' => $entity->get('username')],
            ['user_id' => $entity->get('id')]
        );

        $Entries->updateAll(
            ['edited_by' => $entity->get('username')],
            ['edited_by' => $entity->getOriginal('username')]
        );

        $this->dispatchDbEvent('Cmd.Cache.clear', ['cache' => 'Thread']);
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(\Cake\Event\EventInterface $event, Entity $entity, \ArrayObject $options)
    {
        if ($entity->isDirty('username')) {
            $this->updateUsername($entity);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave(
        \Cake\Event\EventInterface $event,
        Entity $entity,
        \ArrayObject $options
    ) {
        if ($entity->isDirty('password')) {
            $hashedPassword = $this->getPasswordHasher()->hash((string)$entity->get('password'));
            $entity->set('password', $hashedPassword);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeValidate(
        Event $event,
        Entity $entity,
        \ArrayObject $options,
        Validator $validator
    ) {
        if ($entity->isDirty('user_forum_refresh_time')) {
            $time = $entity->get('user_forum_refresh_time');
            if (empty($time)) {
                $entity->set('user_forum_refresh_time', 0);
            }
        }
    }

    /**
     * validate old password
     *
     * @param string $value value
     * @param array $context context
     * @return bool
     */
    public function validateCheckOldPassword($value, array $context)
    {
        $userId = $context['data']['id'];
        $oldPasswordHash = $this->get($userId, ['fields' => ['password']])
            ->get('password');

        return $this->getPasswordHasher()->check((string)$value, $oldPasswordHash);
    }

    /**
     * validate confirm password
     *
     * @param string $value value
     * @param array $context context
     * @return bool
     */
    public function validateConfirmPassword($value, array $context)
    {
        if ($value === $context['data']['password_confirm']) {
            return true;
        }

        return false;
    }

    /**
     * Validate allowed chars
     *
     * @param string $value value
     * @param array $context context
     * @return bool
     */
    public function validateHasAllowedChars($value, array $context)
    {
        foreach ($this->getConfig('user_name_disallowed_chars') as $char) {
            if (mb_strpos($value, $char) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the role exists
     *
     * @param string $value value
     * @param array $context context
     * @return bool|string
     */
    public function validateUserRoleExists($value, array $context)
    {
        /** @var \Saito\User\Permission\Permissions $Permissions */
        $Permissions = Registry::get('Permissions');
        $roles = array_column($Permissions->getRoles()->getAvailable(), 'type');
        if (in_array($value, $roles)) {
            return true;
        }

        return __('vld.user.user_type.allowedType', h($value));
    }

    /**
     * checks if equal username exists
     *
     * @param string $value value
     * @param array $context context
     * @return bool|string
     */
    public function validateUsernameEqual($value, array $context)
    {
        Stopwatch::start('validateUsernameEqual');
        $users = $this->userlist();
        $lc = mb_strtolower($value);
        foreach ($users as $name) {
            if ($name === $value) {
                continue;
            }
            $name = mb_strtolower($name);
            $distance = levenshtein($lc, $name);
            if ($distance < 2) {
                return __('error.name.equalExists', $name);
            }
        }
        Stopwatch::stop('validateUsernameEqual');

        return true;
    }

    /**
     * Registers new user
     *
     * @param array $data data
     * @param bool $activate activate
     * @return \Cake\Datasource\EntityInterface
     */
    public function register($data, $activate = false): EntityInterface
    {
        $defaults = [
            'registered' => bDate(),
            'user_type' => 'user',
        ];
        $fields = [
            'password',
            'registered',
            'user_email',
            'user_type',
            'username',
        ];

        if ($activate !== true) {
            $defaults['activate_code'] = mt_rand(1000000, 9999999);
            $fields[] = 'activate_code';
        }

        $data = array_merge($data, $defaults);

        $fieldFilter = (new FieldFilter())->setConfig('register', $fields);
        if (!$fieldFilter->requireFields($data, 'register')) {
            throw new \RuntimeException(
                'Required fields for registration were not provided.',
                1563789683
            );
        }

        $user = $this->newEntity($data, ['fields' => $fields]);
        $errors = $user->getErrors();
        if (!empty($errors)) {
            return $user;
        }
        $user = $this->save($user);
        if ($user !== false) {
            $this->dispatchDbEvent('saito.core.user.register.after', [
                'subject' => $user,
                'table' => $this,
            ]);
        }

        return $user;
    }

    /**
     * Garbage collection for registration
     *
     * Deletes all timed out and unactivated registrations
     *
     * @return void
     */
    public function registerGc()
    {
        $this->deleteAll(
            [
                'activate_code >' => 0,
                'registered <' => bDate(time() - 86400),
            ]
        );
    }

    /**
     * calls garbage collection for UserBlock
     *
     * UserBlock is lazy-loaded rarely and gc may not trigger often enough (at
     * least with manual blocking and ignore blocking only)
     *
     * @return void
     */
    public function userBlockGc()
    {
        $this->UserBlocks->gc();
    }

    /**
     * activates user
     *
     * @param int $userId user-ID
     * @param string $code activation code
     * @return array|bool false if activation failed; array with status and
     *     user data on success
     * @throws \InvalidArgumentException
     */
    public function activate(int $userId, string $code)
    {
        try {
            $user = $this->get($userId);
        } catch (RecordNotFoundException $e) {
            throw new \InvalidArgumentException();
        }

        $activateCode = strval($user->get('activate_code'));

        if (empty($activateCode)) {
            return ['status' => 'already', 'User' => $user];
        } elseif ($activateCode !== $code) {
            return false;
        }

        $user->set('activate_code', 0);
        $user = $this->save($user);
        if ($user === false) {
            return false;
        }

        $this->dispatchDbEvent('saito.core.user.activate.after', [
            'subject' => $user,
            'table' => $this,
        ]);

        return ['status' => 'activated', 'User' => $user];
    }

    /**
     * Count solved posting for a user.
     *
     *
     * @param int $userId user-ID
     * @return int count
     */
    public function countSolved($userId)
    {
        $count = $this->find()
            ->select(['Users.id'])
            ->where(['Users.id' => $userId])
            ->join(
                [
                    'Entries' => [
                        'table' => $this->Entries->getTable(),
                        'type' => 'INNER',
                        'conditions' => [
                            [
                                'Entries.solves >' => '0',
                                'Entries.user_id' => $userId,
                            ],
                        ],
                    ],
                    'Root' => [
                        'table' => $this->Entries->getTable(),
                        'type' => 'INNER',
                        // Don't answers to own question.
                        'conditions' => [
                            'Root.id = Entries.solves',
                            'Root.user_id != Users.id',
                        ],
                    ],
                ]
            );

        return $count->count();
    }

    /**
     * Set view categories preferences
     *
     * ## $category
     *
     * - 'all': set to all categories
     * - array: (cat_id1 => true|1|'1', cat_id2 => true|1|'1')
     * - int: set to single category_id
     *
     * @param int $userId user-ID
     * @param string|int|array $category category
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setCategory($userId, $category)
    {
        $User = $this->find()->select(['id' => $userId])->first();
        if (!$User) {
            throw new \InvalidArgumentException(
                "Can't find user with id $userId.",
                1420807691
            );
        }

        if ($category === 'all') {
            //=if show all cateogries
            $active = -1;
        } elseif (is_array($category)) {
            //=if set a custom set of categories
            $active = 0;

            $availableCats = $this->Entries->Categories->find('list')->toArray(
            );
            $categories = array_intersect_key($category, $availableCats);
            if (count($categories) === 0) {
                throw new \InvalidArgumentException();
            }
            $newCats = [];
            foreach ($categories as $cat => $v) {
                $newCats[$cat] = ($v === true || $v === 1 || $v === '1');
            }
            $User->set('user_category_custom', $newCats);
        } else {
            //=if set a single category
            $category = (int)$category;
            if (
                $category > 0 && $this->Entries->Categories->exists(['id' => $category])
            ) {
                $active = $category;
            } else {
                throw new \InvalidArgumentException();
            }
        }

        $User->set('user_category_active', $active);
        $this->save($User);
    }

    /**
     * Get default password hasher for hashing user passwords.
     *
     * @return \Authentication\PasswordHasher\PasswordHasherInterface
     */
    public function getPasswordHasher(): PasswordHasherInterface
    {
        return PasswordHasherFactory::build(DefaultPasswordHasher::class);
    }

    /**
     * Finds a user with additional profil informations from associated tables
     *
     * @param \Cake\ORM\Query $query query
     * @param array $options options
     * @return \Cake\ORM\Query
     */
    public function findProfile(Query $query, array $options): Query
    {
        $query
            // ->enableHydration(false)
            ->contain(
                [
                    'UserIgnores' => function ($query) {
                        return $query->enableHydration(false)->select(
                            ['blocked_user_id', 'user_id']
                        );
                    },
                ]
            );

        return $query;
    }

    /**
     * Find all sorted by username
     *
     * @param \Cake\ORM\Query $query query
     * @param array $options options
     * @return \Cake\ORM\Query
     */
    public function findPaginated(Query $query, array $options)
    {
        $query
            ->contain(['UserOnline'])
            ->order(['Users.username' => 'ASC']);

        return $query;
    }

    /**
     * Find the latest, successfully registered user
     *
     * @param \Cake\ORM\Query $query query
     * @param array $options options
     * @return \Cake\ORM\Query
     */
    public function findLatest(Query $query, array $options)
    {
        $query->where(['activate_code' => 0])
            ->order(['id' => 'DESC'])
            ->limit(1);

        return $query;
    }
}
