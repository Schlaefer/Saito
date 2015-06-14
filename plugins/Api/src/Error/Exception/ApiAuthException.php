<?php

namespace Api\Error\Exception;

class ApiAuthException extends GenericApiException
{

    /**
     * {@inheritdoc}
     *
     * @param string $message exception message
     * @return void
     */
    public function __construct($message = '')
    {
        $message = 'Route or action is not authorized.';
        parent::__construct($message);
    }
}
