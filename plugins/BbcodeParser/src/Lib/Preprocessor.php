<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2014-2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib;

class Preprocessor
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
     * Process
     *
     * @param string $string string
     * @return string
     */
    public function process($string)
    {
        return $this->_hashInternalEntryLinks($string);
    }

    /**
     * Convert full internal links (http://domain/…/view/123) to hashes (#123)
     *
     * @param string $string string
     * @return string
     */
    protected function _hashInternalEntryLinks($string)
    {
        $string = preg_replace(
            "%
				(?<!=) # don't hash if part of [url=…
				{$this->_settings->get('server')}{$this->_settings->get('webroot')}{$this->_settings->get('hashBaseUrl')}
				(\d+)  # the id
				%imx",
            "#\\1",
            $string
        );

        return $string;
    }
}
