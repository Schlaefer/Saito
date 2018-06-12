<?php

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Definitions;

use Plugin\BbcodeParser\src\Lib\Helper\Message;

abstract class Html5Media extends CodeDefinition
{
    protected $_sParseContent = false;

    protected static $_html5AudioExtensions = [
        'm4a',
        'ogg',
        'opus',
        'mp3',
        'wav',
    ];

    /**
     * Process audio
     *
     * @param string $content string
     *
     * @return string
     */
    protected function _audio($content)
    {
        $out = "<audio src='$content' controls='controls'>";
        $out .= Message::format(
            __(
                'Your browser does not support HTML5 audio. Please updgrade to a modern ' .
                'browser. In order to watch this stream you need an HTML5 capable browser.',
                true
            )
        );
        $out .= "</audio>";

        return $out;
    }

    /**
     * Process video
     *
     * @param string $content content
     *
     * @return string
     */
    protected function _video($content)
    {
        // fix audio files mistakenly wrapped into an [video] tag
        if (preg_match('/(' . implode('|', self::$_html5AudioExtensions) . ')$/i', $content) === 1) {
            return $this->_audio($content);
        }

        $out = "<video src='$content' controls='controls' x-webkit-airplay='allow'>";
        $out .= Message::format(
            __(
                'Your browser does not support HTML5 video. Please updgrade to a modern ' .
                'browser. In order to watch this stream you need an HTML5 capable browser.',
                true
            )
        );
        $out .= '</video>';

        return $out;
    }
}

//@codingStandardsIgnoreStart
class Html5Audio extends Html5Media
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'audio';

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        return $this->_audio($content);
    }
}

//@codingStandardsIgnoreStart
class Html5Video extends Html5Media
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'video';

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        return $this->_video($content);
    }
}
