<?php

namespace Saito\User\Blocker;

class ManualBlocker extends BlockerAbstract
{

    protected $defaults = [
        // which state to set: block or unblock; null (default): toggle
        'state' => null,
        'adminId' => null,
        'duration' => null
    ];

    public function getReason()
    {
        return 1;
    }

    /**
     * block user manually
     *
     * @param $userId
     * @param array $options
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return bool
     */
    public function block($userId, array $options = [])
    {
        $options += $this->defaults;

        $user = $this->Table->Users->getProfile($userId);
        if (empty($user)) {
            throw new \InvalidArgumentException;
        }
        $conditions = [
            'ended IS' => null,
            'reason' => $this->getReason(),
            'user_id' => $userId
        ];
        if ($options['state'] === null) {
            $existing = $this->Table->find('all', ['conditions' => $conditions])
                ->first();
            $state = empty($existing);
        }
        if ($state) {
            if ($options['adminId']) {
                $conditions['by'] = $options['adminId'];
            }
            if ($options['duration']) {
                $conditions['ends'] = bDate(time() + $options['duration']);
            }
            $entity = $this->Table->newEntity($conditions);
            $success = $this->Table->save($entity);
            if (empty($success)) {
                throw new \Exception;
            }
        } else {
            $this->Table->unblock($existing->get('id'));
        }

        return $state;
    }

}
