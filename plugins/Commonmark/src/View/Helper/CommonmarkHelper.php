<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Commonmark\View\Helper;

use Cake\View\Helper;
use League\CommonMark\CommonMarkConverter;

class CommonmarkHelper extends Helper
{
    protected $_converter;

    /**
     * Parse text as CommonMark
     *
     * @param string $text text to parse
     *
     * @return string
     */
    public function parse($text)
    {
        return $this->_getParser()->convertToHtml($text);
    }

    /**
     * Get parser
     *
     * @return \League\CommonMark\CommonMarkConverter
     */
    protected function _getParser()
    {
        if ($this->_converter !== null) {
            return $this->_converter;
        }
        $this->_converter = new CommonMarkConverter();

        return $this->_converter;
    }
}
