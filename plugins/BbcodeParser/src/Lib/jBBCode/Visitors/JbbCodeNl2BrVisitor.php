<?php

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Visitors;

class JbbCodeNl2BrVisitor extends JbbCodeTextVisitor
{
    /**
     * {@inheritDoc}
     */
    protected function _processTextNode($text, $node)
    {
        return nl2br($text);
    }
}
