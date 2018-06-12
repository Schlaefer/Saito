<?php

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Definitions;

class CodeWithoutAttributes extends CodeDefinition
{
    protected $_sTagName = 'code';

    protected $_sParseContent = false;

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        $type = 'text';
        if (!empty($attributes['code'])) {
            $type = $attributes['code'];
        }

        $this->Geshi->defaultLanguage = 'text';
        // allow all languages
        $this->Geshi->validLanguages = [true];
        // load config from app/Config/geshi.php
        $this->Geshi->features = false;

        $string = '<div class="geshi-wrapper"><pre lang="' . $type . '">' . $content . '</pre></div>';

        $string = $this->Geshi->highlight($string);

        return $string;
    }
}

//@codingStandardsIgnoreStart
class CodeWithAttributes extends CodeWithoutAttributes
//@codingStandardsIgnoreEnd
{
    protected $_sUseOptions = true;
}
