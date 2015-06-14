<?php

namespace App\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type;
use PDO;

class SerializeType extends Type
{

    /**
     * {@inheritDoc}
     */
    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return null;
        }
        if (empty($value)) {
            return [];
        }

        return unserialize($value);
    }

    /**
     * {@inheritDoc}
     */
    public function toDatabase($value, Driver $driver)
    {
        return serialize($value);
    }

    /**
     * {@inheritDoc}
     */
    public function toStatement($value, Driver $driver)
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_STR;
    }
}
