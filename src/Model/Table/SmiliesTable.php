<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppSettingTable;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use \Stopwatch\Lib\Stopwatch;

class SmiliesTable extends AppSettingTable
{

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->hasMany('SmileyCodes', ['foreignKey' => 'smiley_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('order')
            ->add(
                'order',
                ['isNumeric' => ['rule' => 'numeric']]
            );

        return $validator;
    }
}
