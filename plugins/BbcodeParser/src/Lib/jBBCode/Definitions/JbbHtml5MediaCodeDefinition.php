<?php

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Definitions;

use Plugin\BbcodeParser\src\Lib\Helper\UrlParserTrait;
use Plugin\BbcodeParser\src\Lib\jBBCode\Definitions\CodeDefinition;

//@codingStandardsIgnoreStart
class Html5Audio extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    use UrlParserTrait;

    protected $_sTagName = 'audio';

    protected $_sParseContent = false;

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        if (!empty($attributes['src']) && $attributes['src'] === 'upload') {
            $content = $this->_linkToUploadedFile($content);
        }

        return "<audio src='$content' controls='controls' preload='metadata' x-webkit-airplay='allow'></audio>";
    }
}

//@codingStandardsIgnoreStart
class Html5AudioWithAttributes extends Html5Audio
//@codingStandardsIgnoreEnd
{
    protected $_sUseOptions = true;
}

//@codingStandardsIgnoreStart
class Html5Video extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    use UrlParserTrait;

    protected $_sTagName = 'video';

    protected $_sParseContent = false;

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        if (!empty($attributes['src']) && $attributes['src'] === 'upload') {
            $content = $this->_linkToUploadedFile($content);
        }

        return "<video src='$content' controls='controls' preload='metadata' x-webkit-airplay='allow'></video>";
    }
}

//@codingStandardsIgnoreStart
class Html5VideoWithAttributes extends Html5Video
//@codingStandardsIgnoreEnd
{
    protected $_sUseOptions = true;
}
