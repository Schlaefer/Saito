<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Definitions;

//@codingStandardsIgnoreStart
class CodeWithoutAttributes extends CodeDefinition
//@codingStandardsIgnoreEnd
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
