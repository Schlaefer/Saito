<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Lib\Model\Table;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Table;
use Saito\Event\SaitoEventManager;

class AppTable extends Table
{
    use InstanceConfigTrait {
        getConfig as private traitGetConfig;
    }

    /** @var array default config for InstanceConfigTrait */
    protected $_defaultConfig = [];

    public $SharedObjects;

    /**
     * @var SaitoEventManager
     */
    protected $_SEM;

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
    public function dispatchDbEvent($event, $data = [])
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
        if (empty($this->_SEM)) {
            $this->_SEM = SaitoEventManager::getInstance();
        }
        $this->_SEM->dispatch($event, $data + ['Model' => $this]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($key = null, $default = null)
    {
        if (is_string($key)) {
            $setting = Configure::read('Saito.Settings.' . $key);
            if ($setting !== null) {
                return $setting;
            }
        }

        return $this->traitGetConfig($key, $default);
    }
}
