<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\JsData;

class JsData
{
    protected $_appJs = [
        'msg' => []
    ];

    /**
     * get js
     *
     * @return array
     */
    public function getJs()
    {
        return $this->_appJs;
    }

    /**
     * Setter
     *
     * @param string $key string
     * @param mixed $value value
     * @return void
     */
    public function set($key, $value)
    {
        $this->_appJs[$key] = $value;
    }

    /**
     * Add message
     *
     * @param string $message message
     * @param array|null $options options
     * @return void
     */
    public function addMessage(string $message, ?array $options = []): void
    {
        $defaults = [
            'type' => 'notice',
            'channel' => 'notification'
        ];
        $options = array_merge($defaults, $options);

        if (!is_array($message)) {
            $message = [$message];
        }

        foreach ($message as $m) {
            $nm = [
                'message' => $m,
                'type' => $options['type'],
                'channel' => $options['channel']
            ];
            if (isset($options['title'])) {
                $nm['title'] = $options['title'];
            }
            if (isset($options['element'])) {
                $nm['element'] = $options['element'];
            }
            $this->_appJs['msg'][] = $nm;
        }
    }

    /**
     * get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return ['msg' => $this->_appJs['msg']];
    }
}
