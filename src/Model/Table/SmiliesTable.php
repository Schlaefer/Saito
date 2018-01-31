<?php

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
