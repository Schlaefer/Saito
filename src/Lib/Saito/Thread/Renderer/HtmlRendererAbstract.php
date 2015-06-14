<?php

namespace Saito\Thread\Renderer;

use App\View\Helper\PostingHelper;
use Saito\Event\SaitoEventManager;
use Saito\Posting\PostingInterface;

/**
 * renders posting into an ul-li HTML-list tree
 *
 * Check and benchmark on front-page if you perform changes here!
 */
abstract class HtmlRendererAbstract
{

    use \Saito\Posting\Renderer\HelperTrait;

    protected $_Helper;

    protected $_View;

    protected $_defaults = [
        'currentEntry' => null,
        'ignore' => true,
        'rootWrap' => false
    ];

    protected $_settings;

    protected $_lastAnswer;

    protected $_SEM;

    /**
     * Constructor
     *
     * @param PostingHelper $PostingHelper helper
     * @param array $options options
     */
    public function __construct(PostingHelper $PostingHelper, $options = [])
    {
        $this->_Helper = $PostingHelper;
        $this->_View = $this->_Helper->View;
        $this->_SEM = SaitoEventManager::getInstance();
        $this->setOptions($options);
    }

    /**
     * Render init.
     *
     * @param PostingInterface $node posting
     * @return string
     */
    public function render(PostingInterface $node)
    {
        $this->_lastAnswer = $node->getThread()->getLastAnswer();
        $html = $this->_renderNode($node);
        if ($node->isRoot() || $this->_settings['rootWrap']) {
            $html = $this->_wrapUl($html, 0, $node->get('id'));
        }

        return $html;
    }

    /**
     * Setter for options
     *
     * @param array $options options
     * @return void
     */
    public function setOptions($options)
    {
        $this->_settings = $options + $this->_defaults;
    }

    /**
     * Render node.
     *
     * @param PostingInterface $node posting
     * @return string
     */
    protected function _renderNode(PostingInterface $node)
    {
        $html = $this->_renderCore($node);

        $children = $node->getChildren();
        if (empty($children)) {
            return $html;
        }

        $childrenHtml = '';
        foreach ($node->getChildren() as $child) {
            $childrenHtml .= $this->_renderNode($child);
        }
        $level = $node->getLevel();
        $html .= '<li>' . $this->_wrapUl($childrenHtml, $level + 1) . '</li>';

        return $html;
    }

    /**
     * Main render routine for thread.
     *
     * @param PostingInterface $node posting
     * @return string
     */
    abstract protected function _renderCore(PostingInterface $node);

    /**
     * Wraps li tags with ul tag
     *
     * @param string $string li html list
     * @param int $level level
     * @param int $id id
     * @return string
     */
    protected function _wrapUl($string, $level = null, $id = null)
    {
        if ($level >= $this->_settings['maxThreadDepthIndent']) {
            return $string;
        }

        $class = 'threadTree-node';
        $data = '';
        if ($level === 0) {
            $class .= ' root';
            $data = 'data-id="' . $id . '"';
        }

        return "<ul {$data} class=\"{$class}\">{$string}</ul>";
    }

    /**
     * generates CSS classes
     *
     * @param PostingInterface $node posting
     * @return string
     */
    protected function _css($node)
    {
        $entryType = ($node->isRoot()) ? 'et-root' : 'et-reply';
        $entryType .= ($node->isUnread()) ? ' et-new' : ' et-old';
        if ($node->get('id') === (int)$this->_settings['currentEntry']) {
            $entryType .= ' et-current';
        }
        $css = $entryType;
        if ($this->_settings['ignore'] && $node->isIgnored()) {
            $css .= ' ignored';
        }

        return $css;
    }
}
