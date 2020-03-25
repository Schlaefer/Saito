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

use Cake\Http\Exception\HttpException;
use Saito\Exception\Logger\ForbiddenLogger;

class SaitoForbiddenException extends HttpException
{

    /**
     * {@inheritDoc}
     */
    public function __construct($message = null, $data = [])
    {
        $logger = new ForbiddenLogger();
        $logger->write($message, $data);

        $publicMessage = __('exc.forbidden');
        parent::__construct($publicMessage, 403);
    }
}
