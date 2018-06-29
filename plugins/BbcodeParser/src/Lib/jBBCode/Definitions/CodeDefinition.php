<?php

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Definitions;

use Cake\View\Helper;
use JBBCode\ElementNode;
use Saito\Markup\MarkupSettings;

abstract class CodeDefinition extends \JBBCode\CodeDefinition
{

    /**
     * @var Helper calling CakePHP helper
     */
    protected $_sHelper;

    protected $_sParseContent = true;

    protected $_sUseOptions = false;

    /**
     * @var bbcode-tag
     */
    protected $_sTagName;

    /**
     * @var array Saito-options
     */
    protected $_sOptions;

    /**
     * {@inheritDoc}
     */
    public function __construct(Helper $Helper, MarkupSettings $options)
    {
        $this->_sOptions = $options;
        $this->_sHelper = $Helper;
        parent::__construct();
        $this->setTagName($this->_sTagName);
        $this->setParseContent($this->_sParseContent);
        $this->setUseOption($this->_sUseOptions);
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
    public function asHtml(ElementNode $el)
    {
        if (!$this->hasValidInputs($el)) {
            return $el->getAsBBCode();
        }
        $content = $this->getContent($el);
        $parsedString = $this->_parse($content, $el->getAttribute(), $el);
        if ($parsedString === false) {
            return $el->getAsBBCode();
        }

        return $parsedString;
    }

    /**
     * Parse
     *
     * @param string $content content
     * @param array $attributes attributes
     * @param ElementNode $node node
     *
     * @return mixed parsed string or bool false if parsing failed
     */
    abstract protected function _parse($content, $attributes, ElementNode $node);
}
