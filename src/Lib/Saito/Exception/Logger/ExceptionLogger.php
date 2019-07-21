<?php

namespace Saito\Exception\Logger;

use Cake\Log\Log;
use Cake\Routing\Router;
use Saito\User\CurrentUser\CurrentUserInterface;

class ExceptionLogger
{

    private $__lines = [];

    /**
     * Write
     *
     * @param string $message message
     * @param array $data data
     * - `msgs` array with additional message-lines
     * @return void
     * @throws \InvalidArgumentException
     */
    public function write($message, $data = null)
    {
        //# process message(s)
        $msgs = [$message];
        if (isset($data['msgs'])) {
            $msgs = array_merge($msgs, $data['msgs']);
        }
        // prepend main message in front of metadata added by subclasses
        foreach (array_reverse($msgs) as $key => $msg) {
            $this->_add($msg, $key, true);
        }

        //# add exception data
        if (isset($data['e'])) {
            /* @var $Exception \Exception */
            $Exception = $data['e'];
            unset($data['e']);
            $message = $Exception->getMessage();
            if (!empty($message)) {
                $this->_add($message);
            }
        }

        //# add request data
        $request = (php_sapi_name() !== 'cli') ? Router::getRequest() : false;

        $url = false;
        if (isset($data['URL'])) {
            $url = $data['URL'];
        } elseif ($request) {
            $url = $request->getRequestTarget();
        }

        $requestMethod = $request ? $request->getMethod() : false;
        if ($url && $requestMethod) {
            $url .= ' ' . $requestMethod;
        }
        if ($url) {
            $this->_add($url, 'Request URL');
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->_add($_SERVER['HTTP_USER_AGENT'], 'User-Agent');
        }

        $this->_addUser($data);

        if ($request) {
            $data = $request->getData();
            if ($request && !empty($data)) {
                $this->_add($this->_filterData($data), 'Data');
            }
        }

        $this->_write();
    }

    /**
     * adds data about current user to log entry
     *
     * @param mixed $data data
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function _addUser($data)
    {
        if (!isset($data['CurrentUser'])) {
            return;
        }
        if (!($data['CurrentUser'] instanceof CurrentUserInterface)) {
            throw new \InvalidArgumentException;
        }
        $CurrentUser = $data['CurrentUser'];
        if ($CurrentUser->isLoggedIn()) {
            $username = $CurrentUser->get('username');
            $userId = $CurrentUser->getId();
            $username = "{$username} (id: {$userId})";
        } else {
            $username = 'anonymous';
        }
        $this->_add($username, 'Current user');
    }

    /**
     * Filters request-data which should not be in server logs
     *
     * esp. cleartext passwords in $_POST data
     *
     * @param mixed $data data
     * @return array
     */
    protected function _filterData($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $datum) {
            if (is_array($datum)) {
                $data[$key] = $this->_filterData($datum);
                continue;
            }

            if (stripos($key, 'password') !== false) {
                $data[$key] = '***********';
            }
        }

        return $data;
    }

    /**
     * Write
     *
     * @return void
     */
    protected function _write()
    {
        Log::write('error', $this->_message(), ['scope' => ['saito.error']]);
    }

    /**
     * Message
     *
     * @return string
     */
    protected function _message()
    {
        $message = [];
        $i = 1;
        foreach ($this->__lines as $line) {
            $message[] = sprintf("  #%d %s", $i, $line);
            $i++;
        }

        return "\n" . implode("\n", $message);
    }

    /**
     * Add
     *
     * @param mixed $val value
     * @param mixed $key key
     * @param bool $prepend prepend
     * @return void
     */
    protected function _add($val, $key = null, $prepend = false)
    {
        if (is_array($val)) {
            $val = print_r($this->_filterData($val), true);
        }
        if (is_string($key)) {
            $val = "$key: $val";
        }

        if ($prepend) {
            array_unshift($this->__lines, $val);
        } else {
            $this->__lines[] = $val;
        }
    }
}
