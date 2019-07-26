<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib\Processors;

use Saito\Markup\MarkupSettings;

abstract class BbcodeProcessor
{

    protected $_sOptions;

    /**
     * Constructor
     *
     * @param MarkupSettings $options options
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
