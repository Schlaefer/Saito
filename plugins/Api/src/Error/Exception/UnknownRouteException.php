<?php

namespace Api\Error\Exception;

class UnknownRouteException extends GenericApiException
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
            $message = 'Unknown REST route. Check URL and request type (GET, POST, …).';
        }
        parent::__construct($message);
    }
}
