<?php

namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class ContactForm extends Form
{

    protected function _buildSchema(Schema $schema)
    {
        return $schema
            ->addField('subject', 'string')
            ->addField('text', ['type' => 'text'])
            ->addField('cc', ['type' => 'boolean']);
    }

    protected function _buildValidator(Validator $validator)
    {
        return $validator->notEmpty('subject', __('error_subject_empty'));
    }

}
