<?php

namespace Plugin\BbcodeParser\src\Lib\Processors;

use Saito\Markup\MarkupSettings;

abstract class BbcodeProcessor
{

    protected $_sOptions;

    /**
     * Constructor
     *
     * @param array $options options
     */
    public function __construct(MarkupSettings $options)
    {
        $this->_sOptions = $options;
    }

    /**
     * Process
     *
     * @param string $string string to process
     *
     * @return string
     */
    abstract public function process($string);
}
