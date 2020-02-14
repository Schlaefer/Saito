<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
