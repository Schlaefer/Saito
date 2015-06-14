<?php

namespace Api\Error\Exception;

use Cake\Core\Configure;

class ApiValidationException extends GenericApiException
{

    /**
     * {@inheritdoc}
     *
     * @param string $field field with error
     * @param string $rule rule for error
     * @return void
     */
    public function __construct($field, $rule)
    {
        $lookup = $field . ' ' . $rule;
        Configure::write('Config.language', 'en');
        $message = __d('api', $lookup);
        $_noExplanation = $lookup === $message;
        if ($_noExplanation) {
            $message = "Internal validation error. Field: `$field` Rule: `$rule`.";
        }
        parent::__construct($message);
    }
}
