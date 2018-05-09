<?php

namespace Saito;

/**
 * @todo remove this abomination
 */
class JsData
{

    private static $__instance = null;

    protected $_appJs = [
        'msg' => []
    ];

    /**
     * constructor
     */
    protected function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function __clone()
    {
    }

    /**
     * get instance
     *
     * @return null|JsData
     */
    public static function getInstance()
    {
        if (self::$__instance === null) {
            self::$__instance = new JsData();
        }

        return self::$__instance;
    }

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
    public function addAppJsMessage($message, $options = [])
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
    public function getAppJsMessages()
    {
        return ['msg' => $this->_appJs['msg']];
    }
}
