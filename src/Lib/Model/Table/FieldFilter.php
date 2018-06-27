<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Lib\Model\Table;

use Cake\Core\InstanceConfigTrait;

class FieldFilter
{
    use InstanceConfigTrait;

    /**
     * InstanceConfigTrait default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * filters out all fields $fields in $data
     *
     * works only on current model, not associations
     *
     * @param array $data data
     * @param string $action action to use
     * @return void
     */
    public function filterFields(array $data, string $action): array
    {
        $fields = $this->getConfig($action);
        $data = array_intersect_key($data, array_flip($fields));

        return $data;
    }

    /**
     * checks that all $required keys are in array $data
     *
     * @param array $data data
     * @param string $key config-key
     * @return bool false if not all required fields present, true otherwise
     */
    public function requireFields(array $data, string $key): bool
    {
        $required = $this->getConfig($key);

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
    protected function _mapFields($data, $fields, callable $func)
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
}
