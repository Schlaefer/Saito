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

use Saito\Posting\PostingInterface;

/**
 * Class ThreadHtmlRenderer renders postings into a tree
 */
class ThreadHtmlRenderer extends HtmlRendererAbstract
{
    /**
     * @var array performance-cheat for category l10n
     */
    protected static $_catL10n = [];

    /**
     * @var \Saito\Cache\ItemCache
     */
    protected $_LineCache = null;

    protected $_webroot;

    /**
     * {@inheritDoc}
     */
    public function setOptions($options)
    {
        parent::setOptions($options);
        $this->_webroot = $this->_Helper->getView()->getRequest()->getAttribute('webroot');
        if (isset($options['lineCache'])) {
            $this->_LineCache = $options['lineCache'];
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function _renderCore(PostingInterface $node)
    {
        $posting = $node->toArray();
        $level = $node->getLevel();
        $id = $posting['id'];

        $threadLine = $this->_renderThreadLine($node, $posting, $level);
        $css = $this->_css($node);
        $badges = $this->_Helper->getBadges($node);

        $requestedParams = $this->_SEM->dispatch(
            'saito.core.threadline.render.before',
            ['posting' => $posting, 'view' => $this->_View]
        );

        $params = ['css' => '', 'style' => ''];

        if (!empty($requestedParams) && !empty($requestedParams[0])) {
            foreach ($requestedParams as $param) {
                foreach ($param as $key => $value) {
                    $params[$key] .= $value;
                }
            }
        }

        //= manual json_encode() for performance
        $tid = $posting['tid'];
        $isNew = $node->isUnread() ? 'true' : 'false';
        $jsData = <<<EOF
{"id":{$id},"new":{$isNew},"tid":{$tid}}
EOF;

        // data-id still used to identify parent when inserting an inline-answered entry
        // last </span> comes from _renderThreadLine and allows appending to threadLine-post
        $out = <<<EOF
<li class="threadLeaf {$css}" data-id="{$id}" data-leaf='{$jsData}'>
	<div class="threadLine {$params['css']}" style="{$params['style']}">
		<button class="btn btn-link btn_show_thread threadLine-pre et">
			<i class="fa fa-thread"></i>
		</button>
		<a href="{$this->_webroot}entries/view/{$id}"
			class="link_show_thread et threadLine-content">
				{$threadLine} {$badges} </span>
		</a>
	</div>
</li>
EOF;

        return $out;
    }

    /**
     * Render a single threadline
     *
     * @param \Saito\Posting\PostingInterface $node posting
     * @param array $posting posting
     * @param int $level level
     * @return string
     */
    protected function _renderThreadLine(PostingInterface $node, array $posting, int $level)
    {
        $id = $posting['id'];
        $useLineCache = ($level > 0) && ($this->_LineCache !== null);

        if ($useLineCache) {
            $threadLine = $this->_LineCache->get($id);
            if ($threadLine) {
                return $threadLine;
            }
        }

        $subject = $this->_Helper->getSubject($node);
        $username = h($posting['user']->get('username'));
        $time = $this->_Helper->TimeH->formatTime($posting['time']);

        //= category HTML
        $category = '';
        if ($level === 0) {
            $categoryId = $posting['category']['id'];
            if (!isset(self::$_catL10n[$categoryId])) {
                $accession = $posting['category']['accession'];
                $catDesc = h($posting['category']['description']);
                $catTitle = h($posting['category']['category']);
                $category = <<<EOF
<span class="c-category acs-$accession" title="$catDesc">
	($catTitle)
</span>
EOF;
                self::$_catL10n[$categoryId] = $category;
            }
            $category = self::$_catL10n[$categoryId];
        }

        // last </span> closes in parent
        $threadLine = <<<EOF
{$subject}
<span class="c-username"> – {$username}</span>
{$category}
<span class="threadLine-post"> {$time}
EOF;

        if ($useLineCache) {
            $this->_LineCache->set($id, $threadLine, $this->_lastAnswer);
        }

        return $threadLine;
    }
}
