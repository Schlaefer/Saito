<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace BbcodeParser\Lib\jBBCode\Visitors;

use Cake\View\Helper;
use JBBCode\NodeVisitor;
use Saito\Markup\MarkupSettings;

abstract class JbbCodeTextVisitor implements NodeVisitor
{
    protected $_disallowedTags = ['code'];

    /**
     * @var \Cake\View\Helper calling CakePHP helper
     */
    protected $_sHelper;

    protected $_sOptions;

    /**
     * {@inheritDoc}
     */
    public function __construct(Helper $Helper, MarkupSettings $_sOptions)
    {
        $this->_sOptions = $_sOptions;
        $this->_sHelper = $Helper;
    }

    /**
     * {@inheritDoc}
     */
    public function __get($name)
    {
        if (is_object($this->_sHelper->$name)) {
            return $this->_sHelper->{$name};
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitDocumentElement(
        \JBBCode\DocumentElement $documentElement
    ) {
        foreach ($documentElement->getChildren() as $child) {
            $child->accept($this);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitTextNode(\JBBCode\TextNode $textNode)
    {
        $textNode->setValue(
            $this->_processTextNode($textNode->getValue(), $textNode)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitElementNode(\JBBCode\ElementNode $elementNode)
    {
        $tagName = $elementNode->getTagName();
        if (in_array($tagName, $this->_disallowedTags)) {
            return;
        }

        /* We only want to visit text nodes within elements if the element's
         * code definition allows for its content to be parsed.
         */
        $isParsedContentNode = $elementNode->getCodeDefinition()->parseContent(
        );
        if (!$isParsedContentNode) {
            return;
        }

        foreach ($elementNode->getChildren() as $child) {
            $child->accept($this);
        }
    }

    /**
     * {@inheritDoc}
     */
    abstract protected function _processTextNode($text, $textNode);
}
