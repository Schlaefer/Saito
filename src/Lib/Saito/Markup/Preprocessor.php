<?php

namespace Saito\Markup;

abstract class Preprocessor
{

    protected $_settings;

    /**
     * Constructor
     *
     * @param array $settings settings
     */
    public function __construct($settings)
    {
        $this->_settings = $settings;
    }

    /**
     * preprocess markup before it's persistently stored
     *
     * @param string $string string
     * @return string
     */
    abstract public function process($string);
}
