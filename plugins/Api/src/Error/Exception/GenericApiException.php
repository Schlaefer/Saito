<?php

namespace Api\Error\Exception;

use Cake\Network\Exception\BadRequestException;

class GenericApiException extends BadRequestException
{

    /**
     * {@inheritdoc}
     *
     * @param string $message exception message
     * @return void
     */
    public function __construct($message = '')
    {
        if (empty($message)) {
            $message = 'Api Error. Check URL, request type and headers.';
        }
        parent::__construct($message);
    }
}
