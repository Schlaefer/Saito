<?php

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
     * @return CommonMarkConverter
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
