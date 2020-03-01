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

class BbcodePreparePreprocessor extends BbcodeProcessor
{
    /**
     * {@inheritDoc}
     */
    public function process($string)
    {
        $string = h($string);
        // Consolidates '\n\r', '\r' to `\n`
        $string = preg_replace('/\015\012|\015|\012/', "\n", $string);

        return $string;
    }
}
