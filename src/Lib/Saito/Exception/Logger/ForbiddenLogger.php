<?php

namespace Saito\Exception\Logger;

use Cake\Log\Log;

class ForbiddenLogger extends ExceptionLogger
{
    /**
     * {@inheritDoc}
     */
    public function write($message = null, $data = null)
    {
        if (empty($message)) {
            $message = 'Forbidden';
        }

        parent::write($message, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function _write()
    {
        Log::write(
            'error',
            $this->_message(),
            ['scope' => ['saito.forbidden']]
        );
    }
}
