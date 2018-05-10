<?php

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
     * @param array $options options
     * @return void
     */
    public function addMessage($message, $options = [])
    {
        $defaults = [
            'type' => 'notice',
            'channel' => 'notification'
        ];
        if (is_string($options)) {
            $defaults['type'] = $options;
            $options = [];
        }
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
