<?php

namespace Saito\Exception;

use Cake\Http\Exception\BadRequestException;
use Saito\Exception\Logger\ExceptionLogger;

class SaitoBlackholeException extends BadRequestException
{
    /**
     * {@inheritDoc}
     */
    public function __construct($type = null, $data = [])
    {
        $message = 'Request was blackholed. Type: ' . $type;
        $logger = new ExceptionLogger;
        $logger->write($message, $data);
        parent::__construct($message, 400);
    }
}
