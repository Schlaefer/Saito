<?php

namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class ContactForm extends Form
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
            ->addField('subject', 'string')
            ->addField('text', ['type' => 'text'])
            ->addField('cc', ['type' => 'boolean']);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cake\Validation\Validator $validator The validator to customize.
     * @return \Cake\Validation\Validator The validator to use.
     */
    protected function _buildValidator(Validator $validator)
    {
        return $validator->notEmpty('subject', __('error_subject_empty'));
    }
}
