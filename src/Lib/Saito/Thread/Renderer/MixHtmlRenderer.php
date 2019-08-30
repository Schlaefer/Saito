<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Thread\Renderer;

/**
 * Class MixHtmlRenderer renders postings into a mix tree
 */
class MixHtmlRenderer extends HtmlRendererAbstract
{

    /**
     * {@inheritDoc}
     */
    protected function _renderCore(\Saito\Posting\PostingInterface $node)
    {
        $css = $this->_css($node);
        $html = $this->_View->element(
            '/entry/view_posting',
            ['entry' => $node, 'level' => $node->getLevel()]
        );

        $html = <<<EOF
<li id="{$node->get('id')}" class="{$css}">
	<div class="mixEntry panel">{$html}</div>
</li>
EOF;

        return $html;
    }
}
