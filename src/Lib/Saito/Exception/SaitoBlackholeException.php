<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
        $logger = new ExceptionLogger();
        $logger->write($message, $data);
        parent::__construct($message, 400);
    }
}
