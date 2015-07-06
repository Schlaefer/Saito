<?php

namespace Plugin\BbcodeParser\src\Lib\Processors;

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
