<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UserBlocksTable extends Table
{

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        // Blocked user.
        $this->belongsTo('Users', ['foreignKey' => 'user_id']);
        // User responsible for the blocking.
        $this->belongsTo(
            'By',
            ['className' => 'Users', 'foreignKey' => 'blocked_by_user_id']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('ends')
            ->add('ends', 'datetime', ['rule' => ['datetime']]);
        $validator->notEmpty('user_id');
        $validator->notEmpty('reason');
        return $validator;
    }

    /**
     * block user
     *
     * @param BlockerAbstract $Blocker blocker
     * @param int $userId user-ID
     * @param array $options options
     * @return mixed
     */
    public function block($Blocker, $userId, $options)
    {
        $Blocker->setUserBlockTable($this);
        $success = $Blocker->block($userId, $options);
        if ($success) {
            $this->_updateIsBlocked($userId);
        }
        return $success;
    }

    /**
     * get block ending for user
     *
     * @param int $userId user-ID
     * @return mixed
     */
    public function getBlockEndsForUser($userId)
    {
        $block = $this->find(
            'all',
            [
                'conditions' => ['user_id' => $userId, 'ended IS' => null],
                'sort' => ['ends' => 'asc']
            ]
        )->first();
        return $block->get('ends');
    }

    /**
     * unblock
     *
     * @param int $id id
     * @return mixed
     */
    public function unblock($id)
    {
        $block = $this->find()->where(['id' => $id, 'ended IS' => null])->first(
        );
        if (!$block) {
            throw new \InvalidArgumentException;
        }
        $this->patchEntity(
            $block,
            ['ended' => bDate(), 'ends' => null]
        );

        if (!$this->save($block)) {
            throw new \RuntimeException(
                "Couldn't unblock block with id $id.",
                1420540471
            );
        }
        return $this->_updateIsBlocked($block->get('user_id'));
    }

    /**
     * Garbage collection
     *
     * called hourly from User model
     *
     * @return void
     */
    public function gc()
    {
        $expired = $this->find('toGc')->all();
        foreach ($expired as $block) {
            $this->unblock($block->get('id'));
        }
    }

    /**
     * get all
     *
     * @return Query
     */
    public function getAll()
    {
        $blocklist = $this->find('assocUsers')
            ->order(['UserBlocks.id' => 'DESC']);
        return $blocklist;
    }

    /**
     * gc finder
     *
     * @param Query $query query
     * @param array $options options
     * @return Query
     */
    public function findToGc(Query $query, array $options)
    {
        $query->where(
            [
                'ends IS NOT' => null,
                'ends <' => bDate(),
                'ended IS' => null
            ]
        );
        return $query;
    }

    /**
     * Select required fields from associated Users.
     *
     * Don't hydrate full user entities.
     *
     * @param Query $query query
     * @return Query
     */
    public function findAssocUsers(Query $query)
    {
        $callback = function (Query $query) {
            return $query->select(['id', 'username']);
        };
        $query->contain(['By' => $callback, 'Users' => $callback]);
        return $query;
    }

    /**
     * update is blocked
     *
     * @param int $userId user-ID
     * @return mixed
     */
    protected function _updateIsBlocked($userId)
    {
        $blocks = $this->find(
            'all',
            [
                'conditions' => [
                    'ended IS' => null,
                    'user_id' => $userId
                ]
            ]
        )->first();
        $user = $this->Users->get($userId, ['fields' => ['id', 'user_lock']]);
        $user->set('user_lock', !empty($blocks));
        return $this->Users->save($user);
    }
}
