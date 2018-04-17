<?php

namespace Saito\Exception;

use Cake\Http\Exception\HttpException;
use Saito\Exception\Logger\ForbiddenLogger;

class SaitoForbiddenException extends HttpException
{

    protected $__Logger;

    /**
     * {@inheritDoc}
     */
    public function __construct($message = null, $data = [])
    {
        $this->__Logger = new ForbiddenLogger;
        $this->__Logger->write($message, $data);
        parent::__construct($message, 403);
    }
}
