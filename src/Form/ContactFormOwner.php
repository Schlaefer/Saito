<?php

namespace App\Form;

use Cake\Form\Schema;
use Cake\Validation\Validator;

class ContactFormOwner extends ContactForm
{

    protected function _buildSchema(Schema $schema)
    {
        $schema = parent::_buildSchema($schema);
        $schema->addField('sender_contact', 'string');

        return $schema;
    }

    protected function _buildValidator(Validator $validator)
    {
        $validator = parent::_buildValidator($validator);
        $validator
            ->notEmpty('sender_contact')
            ->add('sender_contact', [
                'isEmail' => [
                    'rule' => ['email', true],
                    'message' => __('error_email_not-valid')
                ]
            ]);

        return $validator;
    }

}
