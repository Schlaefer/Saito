<?php

namespace Saito\Exception\Logger;

use Cake\Log\Log;

class ForbiddenLogger extends ExceptionLogger
{

    /**
     * @param null $message
     * @param null|array $data
     * - `CurrentUser`
     * - `msgs` array with additional message-lines
     * @throws \InvalidArgumentException
     */
    public function write($message = null, $data = null)
    {
        if (empty($message)) {
            $message = 'Forbidden';
        }

        parent::write($message, $data);
    }

    protected function _write()
    {
        Log::write('error', $this->_message(), ['scope' => ['saito.forbidden']]);
    }

}

