<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class BlockForm extends Form
{

    /**
     * {@inheritdoc}
     *
     * @param \Cake\Form\Schema $schema The schema to customize.
     * @return \Cake\Form\Schema The schema to use.
     */
    protected function _buildSchema(Schema $schema)
    {
        return $schema
            ->addField('lockPeriod', ['type' => 'string'])
            ->addField('lockUserId', ['type' => 'string']);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cake\Validation\Validator $validator The validator to customize.
     * @return \Cake\Validation\Validator The validator to use.
     */
    protected function _buildValidator(Validator $validator)
    {
        $validator
            ->notEmpty('lockPeriod')
            ->add('lockPeriod', ['isNumeric' => ['rule' => ['numeric']]])
            ->notEmpty('lockUserId')
            ->add('lockUserId', ['isNumeric' => ['rule' => ['numeric']]]);

        return $validator;
    }
}
