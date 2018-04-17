<?php

namespace Bookmarks\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class BookmarksTable extends Table
{

    /**
     * {@inheritdoc}
     *
     * @param array $config config
     * @return void
     */
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->belongsTo('Entries', ['foreignKey' => 'entry_id']);
        $this->belongsTo('Users', ['foreignKey' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cake\Validation\Validator $validator validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->requirePresence('entry_id', 'create')
            ->add(
                'entry_id',
                [
                    'exists' => [
                        'rule' => [$this, 'validatePostingExists'],
                        'last' => true,
                    ],
                    'numeric' => ['rule' => 'numeric', 'last' => true],
                    'unique' => [
                        'rule' => [$this, 'validateUniqueBookmark'],
                        'last' => true,
                        'on' => 'create'
                    ]
                ]
            )
            ->requirePresence('user_id', 'create')
            ->add(
                'user_id',
                [
                    'exists' => [
                        'rule' => [$this, 'validateUserExists'],
                        'last' => true,
                    ],
                    'numeric' => ['rule' => 'numeric', 'last' => true],
                ]
            );

        return $validator;
    }

    /**
     * Check if user exists.
     *
     * @param int $value user-ID
     * @return bool valid
     */
    public function validateUserExists($value)
    {
        return $this->Users->exists(['id' => $value]);
    }

    /**
     * Check if posting exists.
     *
     * @param int $value posting-ID
     * @return bool valid
     */
    public function validatePostingExists($value)
    {
        return $this->Entries->exists(['id' => $value]);
    }

    /**
     * Validate that combination of entry_id and user_id is unique.
     *
     * @param int $value value
     * @param array $context context
     * @return bool
     */
    public function validateUniqueBookmark($value, $context)
    {
        $data = $context['data'];
        if (empty($data['user_id'])) {
            return true;
        }
        $conditions = [
            'entry_id' => $data['entry_id'],
            'user_id' => $data['user_id']
        ];

        return !$this->exists($conditions);
    }

    /**
     * Create and save new bookmark.
     *
     * @param array $data bookmark data
     * @return bool|\Cake\Datasource\EntityInterface
     */
    public function createBookmark(array $data)
    {
        $bookmark = $this->newEntity(
            $data,
            ['fields' => ['entry_id', 'user_id']]
        );
        if ($bookmark->getErrors()) {
            return $bookmark;
        }

        return $this->save($bookmark);
    }
}
