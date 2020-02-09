<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use PDO;

class SerializeType extends BaseType
{

    /**
     * {@inheritDoc}
     */
    public function toPHP($value, DriverInterface $driver)
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
    public function toDatabase($value, DriverInterface $driver)
    {
        return serialize($value);
    }

    /**
     * {@inheritDoc}
     */
    public function toStatement($value, DriverInterface $driver)
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_STR;
    }

    /**
     * {@inheritDoc}
     */
    public function marshal($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
