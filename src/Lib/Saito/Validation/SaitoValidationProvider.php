<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Validation;

class SaitoValidationProvider
{
    /**
     * validator checking if value is unique case insensitive
     *
     * simplified version Cake\ORM\Table\validateUnique and ORM\Rule\isUnique
     *
     * @param string $value value
     * @param array $context context
     * @return bool
     */
    public static function validateIsUniqueCiString($value, array $context)
    {
        $field = $context['field'];
        $Table = $context['providers']['table'];
        $primaryKey = $Table->getPrimaryKey();

        $conditions = ["LOWER($field)" => mb_strtolower($value)];
        if ($context['newRecord'] === false) {
            $conditions['NOT'] = [$primaryKey => $context['data'][$primaryKey]];
        }

        return !$Table->exists($conditions);
    }
}
