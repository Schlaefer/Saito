<?php

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
