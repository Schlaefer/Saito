<?php

namespace Api\Error\Exception;

class ApiDisabledException extends GenericApiException
{

    /**
     * {@inheritdoc}
     *
     * @param string $message exception message
     * @return void
     */
    public function __construct($message = '')
    {
        $message = 'API is disabled.';
        parent::__construct($message);
    }
}
