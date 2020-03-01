<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace BbcodeParser\Lib\Processors;

class BbcodeQuotePostprocessor extends BbcodeProcessor
{
    /**
     * {@inheritDoc}
     */
    public function process($string)
    {
        $quoteSymbolSanitized = h($this->_sOptions->get('quote_symbol'));
        $string = preg_replace(
            // Begin of the text or a new line in the text, maybe one space afterwards
            '/(^|\n\r\s?)' .
            $quoteSymbolSanitized .
            '\s(.*)(?!\<br)/m',
            "\\1<span class=\"richtext-citation\">" . $quoteSymbolSanitized . " \\2</span>",
            $string
        );

        return $string;
    }
}
