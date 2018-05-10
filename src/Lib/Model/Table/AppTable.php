<?php

namespace App\Lib\Model\Table;

use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Saito\Event\SaitoEventManager;

class AppTable extends Table
{

    /**
     * @var array model settings; can be overwritten by DB or config Settings
     */
    protected $_settings = [];

    public $SharedObjects;

    /**
     * @var SaitoEventManager
     */
    protected $_SEM;

    /**
     * Toggle bool field value.
     *
     * @param int $recordId The record-ID.
     * @param string $field The name of the field to toggle.
     * @return int new field The new value after the toggle.
     */
    public function toggle($recordId, $field)
    {
        $entity = $this->query()
            ->select(['id', $field])
            ->where(['id' => $recordId])
            ->first();
        $new = ($entity->get($field) == 0) ? 1 : 0;
        $this->patchEntity($entity, [$field => $new]);
        $this->save($entity);

        return $new;
    }

    /**
     * Check that a record exists.
     *
     * @param int|array|\ArrayAccess $conditions Record-ID or query conditions.
     * @return bool
     */
    public function exists($conditions)
    {
        if (is_int($conditions)) {
            $conditions = ['id' => $conditions];
        }

        return parent::exists($conditions);
    }

    /**
     * filters out all fields $fields in $data
     *
     * works only on current model, not associations
     *
     * @param array $data data
     * @param array $fields fields
     * @return void
     */
    public function filterFields(&$data, $fields)
    {
        $fields = array_flip($fields);
        $data = array_intersect_key($data, $fields);
    }

    /**
     * checks that all $required keys are in array $data
     *
     * @param array $data data
     * @param array $required required
     * @return bool false if not all required fields present, true otherwise
     */
    public function requireFields(&$data, array $required)
    {
        return $this->_mapFields(
            $data,
            $required,
            function (&$data, $field, $model = null) {
                if ($model === null) {
                    if (!isset($data[$field])) {
                        return false;
                    }
                }

                return true;
            }
        );
    }

    /**
     * Filter fields
     *
     * @param array $data to map
     * @param array $fields fields
     * @param callable $func The callback.
     * @return bool
     */
    protected function _mapFields(&$data, $fields, callable $func)
    {
        $isArrayWithMultipleResults = isset(reset($data)[reset($fields)]);
        if ($isArrayWithMultipleResults) {
            foreach ($data as &$d) {
                if (!$this->_mapFields($d, $fields, $func)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($fields as $field) {
            if (!$func($data, $field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increments value of a field
     *
     * @param int|array $where entry-id or array with conditions
     * @param string $field fielt to increment
     * @param int $amount Increment size.
     * @return void
     * @throws \InvalidArgumentException
     */
    public function increment($where, $field, $amount = 1)
    {
        if (!is_int($amount)) {
            throw new \InvalidArgumentException;
        }

        if (is_numeric($where)) {
            $where = ['id' => $where];
        }

        $operator = '+';
        if ($amount < 0) {
            $operator = '-';
            $amount *= -1;
        }
        $expression = new QueryExpression("$field = $field $operator $amount");
        $this->updateAll($expression, $where);
    }

    /**
     * Dispatches an event
     *
     * - Always passes the issuing model class as subject
     * - Wrapper for CakeEvent boilerplate code
     * - Easier to test
     *
     * @param string $event event identifier `Model.<modelname>.<event>`
     * @param array $data additional event data
     * @return void
     */
    protected function _dispatchEvent($event, $data = [])
    {
        EventManager::instance()->dispatch(new Event($event, $this, $data));
        // propagate event on Saito's event bus
        $this->dispatchSaitoEvent($event, $data);
    }

    /**
     * Dispatch Saito Event
     *
     * @param string $event event
     * @param array $data data
     * @return void
     */
    public function dispatchSaitoEvent($event, $data)
    {
        if (!$this->_SEM) {
            $this->_SEM = SaitoEventManager::getInstance();
        }
        $this->_SEM->dispatch($event, $data + ['Model' => $this]);
    }

    /**
     * gets app setting
     *
     * falls back to local definition if available
     *
     * @param string $name setting
     * @return mixed
     * @throws \UnexpectedValueException
     */
    protected function _setting($name)
    {
        $setting = Configure::read('Saito.Settings.' . $name);
        if ($setting !== null) {
            return $setting;
        }
        if (isset($this->_settings[$name])) {
            return $this->_settings[$name];
        }
        throw new \UnexpectedValueException;
    }
}
