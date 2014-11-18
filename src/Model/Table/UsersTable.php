<?php

	namespace App\Model\Table;

	use App\Lib\Model\Table\AppTable;
	use Cake\Auth\DefaultPasswordHasher;
    use Cake\Auth\PasswordHasherFactory;
    use Cake\Database\Schema\Table as Schema;
    use Cake\Datasource\Exception\RecordNotFoundException;
    use Cake\Event\Event;
	use Cake\ORM\Entity;
	use Cake\ORM\Query;
	use Cake\Validation\Validator;
	use Cake\Utility\Hash;
    use Saito\User\Upload\AvatarFilenameListener;
    use Stopwatch\Lib\Stopwatch;

	class UsersTable extends AppTable {

		// @todo 3.0
		public $hasMany = array(
			'Esnotification' => array(
				'foreignKey' => 'user_id'
			),
			'Upload' => array(
				'className' => 'Upload',
				'foreignKey' => 'user_id'
			)
		);

		// @todo 3.0
		public $validate = [
				'user_type' => [
						'allowedChoice' => ['rule' => ['inList', ['user', 'admin', 'mod']]]
				],
				'registered' => ['rule' => ['notEmpty']],
				'logins' => ['rule' => 'numeric'],
				'personal_messages' => ['rule' => ['boolean']],
				'user_lock' => ['rule' => ['boolean']],
				'activate_code' => [
						'numeric' => ['rule' => 'numeric', 'allowEmpty' => false],
						'between' => ['rule' => ['between', 0, 9999999]]
				],
				'user_signatures_hide' => ['rule' => ['boolean']],
				'user_signature_images_hide' => ['rule' => ['boolean']],
				'user_automaticaly_mark_as_read' => ['rule' => ['boolean']],
				'user_sort_last_answer' => ['rule' => ['boolean']],
				'user_color_new_postings' => [
					'allowEmpty' => true,
					'rule' => '/^#?[a-f0-9]{0,6}$/i'
				],
				'user_color_old_postings' => [
						'allowEmpty' => true,
						'rule' => '/^#?[a-f0-9]{0,6}$/i',
						'message' => '*'
				],
				'user_color_actual_posting' => [
					'allowEmpty' => true,
					'rule' => '/^#?[a-f0-9]{0,6}$/i'
				],
		];

        /**
         * @var array password hasher
         */
		protected $_passwordHasher = [
			'default' => 'Cake\Auth\DefaultPasswordHasher',
			'App\Auth\Mlf2PasswordHasher',
			'App\Auth\MlfPasswordHasher'
		];

		protected $_settings = [
			'user_name_disallowed_chars' => ['\'', ';', '&', '<', '>']
		];

		public function initialize(array $config) {
            $this->addBehavior(
                'Cron.Cron',
                [
                    'registerGc' => [
                        'id' => 'User.registerGc',
                        'due' => 'daily',
                    ],
                    'userBlockGc' => [
                        'id' => 'User.userBlockGc',
                        'due' => '+15 minutes',
                    ]
                ]
            );

            $this->addBehavior(
                'Proffer.Proffer',
                [
                    'avatar' => [    // The name of your upload field
                        'root' => WWW_ROOT . 'useruploads',
                        'dir' => 'avatar_dir',
                        'thumbnailSizes' => [
                            'square' => ['w' => 100, 'h' => 100],
                        ],
                        // Options are Imagick, Gd or Gmagick
                        'thumbnailMethod' => 'Gd'
                    ]
                ]
            );
            $this->eventManager()->on(new AvatarFilenameListener());


			$this->hasOne('UserOnline', ['foreignKey' => 'user_id']);

            $this->hasMany(
                'Bookmarks',
                ['foreignKey' => 'user_id', 'dependent' => true]
            );
            $this->hasMany('UserIgnores', ['foreignKey' => 'user_id']);
			$this->hasMany(
				'Entries',
				[
					'foreignKey' => 'user_id',
					'conditions' => ['Entries.user_id' => 'Users.id'],
				]
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
						'UserBlocks.ended DESC',
						'UserBlocks.id DESC'
					]
				]
			);
            $this->hasMany('Uploads', ['foreign_key' => 'user_id']);
		}

		public function validationDefault(Validator $validator) {
            $validator->provider(
                'saito',
                'Saito\Validation\SaitoValidationProvider'
            );


            $validator
                ->provider(
                    'proffer', 'Proffer\Model\Validation\ProfferRules'
                )
                ->allowEmpty('avatar_dir')
                ->allowEmpty('avatar')
                ->add(
                    'avatar',
                    'proffer',
                    ['rule' => ['filesize', 300000], 'provider' => 'proffer']
                )
                ->add(
                    'avatar',
                    'proffer',
                    [
                        'rule' => ['extension', ['jpg', 'jpeg', 'png']],
                        'message' => 'Invalid extension',
                        'provider' => 'proffer'
                    ]
                )
                ->add(
                    'avatar',
                    'proffer',
                    [
                        'rule' => ['mimetype', ['image/jpeg', 'image/png']],
                        'message' => 'Not the correct mime type',
                        'provider' => 'proffer'
                    ]
                )
                ->add(
                    'avatar',
                    'proffer',
                    [
                        'rule' => ['dimensions', [
                            'min' => ['w' => 100, 'h' => 100],
                            'max' => ['w' => 1500, 'h' => 1500]
                        ]],
                        'message' => 'Image is not correct dimensions.',
                        'provider' => 'proffer'
                    ]
                );

            $validator
                ->notEmpty('password')
                ->add(
                    'password',
                    [
                        'pwConfirm' => [
                            'rule' => [$this, 'validateConfirmPassword'],
                            'last' => true,
                            'message' => __('error_password_confirm')
                        ]
                    ]
                );

            $validator
                ->notEmpty('password_old')
                ->add(
                    'password_old',
                    [
                        'pwCheckOld' => [
                            'rule' => [$this, 'validateCheckOldPassword'],
                            'last' => true,
                            'message' => 'validation_error_pwCheckOld'
                        ]
                    ]
                );

			$validator
				->notEmpty('username', __('error_no_name'))
				->add(
					'username',
					[
						'isUnique' => [
							'rule' => 'validateIsUniqueCiString',
							'provider' => 'saito',
                            'message' => __('error_name_reserved')
						],
						'isUsernameEqual' => [
							'on' => 'create',
							'rule' => [$this, 'validateUsernameEqual']
						],
						'hasAllowedChars' => [
                            'rule' => [$this, 'validateHasAllowedChars'],
                            'message' => __('model.user.validate.username.hasAllowedChars')
                        ]
					]
				);

            $validator
                ->notEmpty('user_email')
                ->add(
                    'user_email',
                    [
                        'isUnique' => [
                            'rule' => 'validateUnique',
                            'provider' => 'table',
                            'last' => true,
                            'message' => __('error_email_reserved')
                        ],
                        'isEmail' => [
                            'rule' => ['email', true],
                            'last' => true,
                            'message' => __('error_email_wrong')
                        ]
                    ]
                );

            $validator
                ->allowEmpty('user_place_lat')
                ->add(
                    'user_place_lat',
                    ['validLatitude' => ['rule' => ['range', -90, 90]]]
                );

            $validator
                ->allowEmpty('user_place_lng')
                ->add(
                    'user_place_lng',
                    ['validLongitude' => ['rule' => ['range', -180, 180]]]
                );

            $validator
                ->allowEmpty('user_place_zoom')
                ->add(
                    'user_place_zoom',
                    [
                        'between' => ['rule' => ['range', 0, 25]],
                        'numeric' => ['rule' => ['naturalNumber']]
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

			return $validator;
		}

        protected function _initializeSchema(Schema $table)
        {
            $table->columnType('avatar', 'proffer.file');
            $table->columnType('user_category_custom', 'serialize');
            return $table;
        }

        /**
 * @param null $lastRefresh
 *
 * @throws Exception
 */
		public function setLastRefresh($userId, $lastRefresh = null) {
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

			// @todo 3.0
			/*
			if ($success == false) {
				throw new Exception('Updating last user refresh failed.');
			}
			*/
			Stopwatch::end('Users->setLastRefresh()');
		}

		public function incrementLogins(Entity $user, $amount = 1) {
			$data = [
				'logins' => $user->get('logins') + $amount,
				'last_login' => bDate()
			];
			$this->patchEntity($user, $data);
			if (!$this->save($user)) {
				throw new Exception('Increment logins failed.');
			}
		}

		public function userlist() {
			return $this->find(
				'list',
				['keyField' => 'id', 'valueField' => 'username']
			)->toArray();
		}

/**
 * Removes a user and all his data execpt for his entries
 *
 * @param int $userId user-ID
 * @return boolean
 */
		public function deleteAllExceptEntries($userId) {
			if ($userId == 1) {
				return false;
			}
            $user = $this->get($userId);
            if (!$user) {
                return false;
            }

            try {
                $this->Uploads->deleteAllFromUser($userId);
                // @todo 3.0 notifications
//			$success = $success && $this->Esnotification->deleteAllFromUser($userId);
                $this->Entries->anonymizeEntriesFromUser($userId);
                $this->UserIgnores->deleteUser($userId);
                $this->UserOnline->deleteAll(['user_id' => $userId]);
                $this->delete($user);
            } catch (\Exception $e) {
                return false;
            }
            $this->_dispatchEvent('Cmd.Cache.clear', ['cache' => 'Thread']);
			return true;
		}

        /**
         * updates non-blowfish-hash to current hashing method
         *
         * @param int $userId
         * @param string $password
         */
        public function autoUpdatePassword($userId, $password) {
            $Entity = $this->get($userId, ['fields' => ['id', 'password']]);
			$oldPassword = $Entity->get('password');
            $hasher = new $this->_passwordHasher['default'];
			if (!$hasher->needsRehash($oldPassword)) {
                return;
            }
            $Entity->set('password', $password);
            $this->save($Entity);
		}

		public function afterSave(Event $event, Entity $entity, \ArrayObject $options) {
            if ($entity->getOriginal('username')) {
                $this->_dispatchEvent('Cmd.Cache.clear', ['cache' => 'Thread']);
            }
		}

		public function beforeSave(Event $event, Entity $entity, \ArrayObject $options) {
			if ($entity->dirty('password')) {
				$hashedPassword = $this->_hashPassword($entity->get('password'));
				$entity->set('password', $hashedPassword);
			}
		}

		public function beforeValidate(Event $event, Entity $entity, \ArrayObject $options, Validator $validator) {
			if ($entity->dirty('user_forum_refresh_time')) {
				$time = $entity->get('user_forum_refresh_time');
				if (empty($time)) {
					$entity->set('user_forum_refresh_time', 0);
				}
			}
		}

		public function validateCheckOldPassword($value, array $context) {
            $userId = $context['data']['id'];
            $oldPassword = $this->get($userId, ['fields' => ['password']])
                ->get('password');
			return $this->checkPassword($value, $oldPassword);
		}

		public function validateConfirmPassword($value, array $context) {
			if ($value === $context['data']['password_confirm']) {
				return true;
			}
			return false;
		}

		public function validateHasAllowedChars($value, array $context) {
			foreach ($this->_setting('user_name_disallowed_chars') as $char) {
				if (mb_strpos($value, $char) !== false) {
					return false;
				}
			}
			return true;
		}

		/**
		 * checks if equal username exists
		 */
		public function validateUsernameEqual($value) {
			Stopwatch::start('validateUsernameEqual');
			$users = $this->userlist();
			foreach ($users as $name) {
				if ($name === $value) {
					continue;
				}
				$distance = levenshtein($value, $name);
				if ($distance < 2) {
                    return __('error.name.equalExists', $name);
				}
			}
			Stopwatch::stop('validateUsernameEqual');
			return true;
		}

		public function paginate($conditions, $fields, $order, $limit, $page = 1, $recursive = null, $extra = array()) {
			$username = $this->alias . '.' . 'username';
			if (isset($order[$username])) {
				$direction = $order[$username];
				unset($order[$username]);
			} else {
				$direction = 'asc';
			}
			$order['LOWER(User.username)'] = $direction;

			// merge $extras for 'contain' parameter
			$params = array_merge(compact('conditions', 'fields', 'order', 'limit',
				'page', 'recursive', 'group'), $extra);

			return $this->find('all', $params);
		}

        /**
         * Registers new user
         *
         * @param array $data
         * @param bool $activate
         * @return new user entity
         */
		public function register($data, $activate = false) {
			$defaults = [
				'registered' => bDate(),
				'user_type' => 'user'
			];
			$fields = [
				'password',
				'registered',
				'user_email',
				'user_type',
				'username'
			];

			if ($activate !== true) {
				$defaults['activate_code'] = mt_rand(1000000, 9999999);
				$fields[] = 'activate_code';
			}

			$data = array_merge($data, $defaults);

			if (!$this->requireFields($data, $fields) || !$this->unsetFields($data)) {
				return false;
			}

			$user = $this->newEntity($data, ['fieldList' => $fields]);
            $errors = $user->errors();
            if (!empty($errors)) {
                return $user;
            }
            $this->save($user);
            return $user;
		}

/**
 * Garbage collection for registration
 *
 * Deletes all timed out and unactivated registrations
 */
        public function registerGc() {
            $this->deleteAll(
                [
                    'activate_code >' => 0,
                    'registered <' => bDate(time() - 86400)
                ]
            );
		}

		/**
		 * calls garbage collection for UserBlock
		 *
		 * UserBlock is lazy-loaded rarely and gc may not trigger often enough (at
		 * least with manual blocking and ignore blocking only)
		 */
		public function userBlockGc() {
			$this->UserBlocks->gc();
		}

        /**
         * activates user
         *
         * @param $userId user-ID
         * @param $code activation code
         * @return array|bool false if activation failed; array with status and user data on success
         * @throws InvalidArgumentException
         */
		public function activate($userId, $code) {
			if (!is_int($userId) || !is_string($code)) {
				throw new \InvalidArgumentException();
			}

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
            $success = $this->save($user);
			if (empty($success)) {
				return false;
			}

			$this->_dispatchEvent('Model.User.afterActivate', ['User' => $user]);

			return ['status' => 'activated', 'User' => $user];
		}

/**
 *
 * @param int $id user-id
 * @return bool|array false if not found, array otherwise
 */
		public function getProfile($id) {
			// @perf Ignore is currently retrieved via second query, consider moving
			// it as cache into user-table
			$user = $this->find()
				->hydrate(false)
				->contain(['UserIgnores' => function($query) {
						return $query->hydrate(false)->select(['blocked_user_id', 'user_id']);
					}])
				->where(['id' => $id])
				->first();
			if ($user) {
				/*
				// @todo 3.0 make this nicer
				$ignoredIds = [];
				$ignores = $user->get('user_ignores');
				if ($ignores) {
					foreach ($ignores as $ignore) {
						$id = $ignore->get('id');
						$ignoredIds[$id] = $id;
					}
				}
				$user = $user->set('ignores', $ignoredIds);
				*/
				$user = $user + [
						'ignores' => array_fill_keys(
							Hash::extract($user, 'user_ignores.{n}.blocked_user_id'), 1
						)
					];
			}
			return $user;
		}

		public function countSolved($id) {
			$count = $this->find()
				->select(['Users.id'])
				->where(['Users.id' => $id])
				->join(
					[
						'Entries' => [
							'table' => $this->Entries->table(),
							'type' => 'INNER',
							'conditions' => [
								[
									'Entries.solves >' => '0',
									'Entries.user_id' => $id
								]
							],
						],
						'Root' => [
							'table' => $this->Entries->table(),
							'type' => 'INNER',
							'conditions' => [
								'Root.id = Entries.solves',
								'Root.user_id != Users.id',
							]
						]
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
		 * @param int $userId
		 * @param string|int|array $category
		 * @throws \InvalidArgumentException
		 */
		public function setCategory($userId, $category) {
			$User = $this->find()->select(['id' => $userId])->first();
			if (!$User) {
				throw new \InvalidArgumentException(
					"Can't find user with id $userId. 1420807691"
				);
			}

			if ($category === 'all') {
				//=if show all cateogries
				$active = -1;
			} elseif (is_array($category)) {
				//=if set a custom set of categories
				$active = 0;

				$availableCats = $this->Entries->Categories->find('list')->toArray();
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
				if ($category > 0 && $this->Entries->Categories->exists(
						(int)$category
					)
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
		 * Checks if password is valid against all supported auth methods
		 *
		 * @param string $password
		 * @param string $hash
		 * @return boolean TRUE if password match FALSE otherwise
		 */
		public function checkPassword($password, $hash) {
			foreach ($this->_passwordHasher as $passwordHasher) {
				$hasher = PasswordHasherFactory::build($passwordHasher);
				if ($hasher->check($password, $hash)) {
					return true;
				}
			}
            return false;
		}

		/**
		 * Custom hash function used for authentication with Auth component
		 *
		 * @param string $password
		 * @return string hashed password
		 */
		protected function _hashPassword($password) {
			$auth = new DefaultPasswordHasher();
			return $auth->hash($password);
		}

        /**
         * Find all sorted by username
         *
         * @param Query $query
         * @param array $options
         * @return Query
         */
        public function findPaginated(Query $query, array $options) {
            $query
                ->contain(['UserOnline'])
                ->order(['Users.username' => 'ASC']);
            return $query;
        }

		/**
		 * Find the latest, successfully registered user
		 *
		 * @param Query $query
		 * @param array $options
		 * @return Query
		 */
		public function findLatest(Query $query, array $options) {
			$query->where(['activate_code' => 0])
				->order(['id' => 'DESC'])
				->limit(1);
			return $query;
		}

	}
