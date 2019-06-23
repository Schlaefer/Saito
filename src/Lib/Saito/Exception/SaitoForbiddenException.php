<?php

namespace Saito\Exception;

use Cake\Http\Exception\HttpException;
use Saito\Exception\Logger\ForbiddenLogger;

class SaitoForbiddenException extends HttpException
{

    /**
     * {@inheritDoc}
     */
    public function __construct($message = null, $data = [])
    {
        $logger = new ForbiddenLogger;
        $logger->write($message, $data);
        parent::__construct($message, 403);
    }
}
