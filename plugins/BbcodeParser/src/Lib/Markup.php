<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib;

use App\View\Helper\ParserHelper;
use Saito\Markup\MarkupInterface;
use Saito\Markup\MarkupSettings;

class Markup implements MarkupInterface
{
    /**
     * @var \Plugin\BbcodeParser\src\Lib\Editor|null
     */
    protected $editor;
    /**
     * @var \Plugin\BbcodeParser\src\Lib\Parser|null
     */
    protected $parser;
    /**
     * @var \Plugin\BbcodeParser\src\Lib\Preprocessor|null
     */
    protected $preproccesor;
    /**
     * @var \Saito\Markup\MarkupSettings|null
     */
    protected $settings;

    /**
     * {@inheritDoc}
     */
    public function __construct(MarkupSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritDoc}
     */
    public function getEditorHelp(ParserHelper $helper): string
    {
        return $this->getEditor()->getEditorHelp($helper);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarkupSet(): array
    {
        return $this->getEditor()->getMarkupSet();
    }

    /**
     * {@inheritDoc}
     */
    public function parse(string $string, ParserHelper $helper, array $options = []): string
    {
        if (!$this->parser) {
            $this->parser = new Parser($helper, $this->settings);
        }

        return $this->parser->parse($string, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function preprocess($string): string
    {
        if (!$this->preproccesor) {
            $this->preproccesor = new Preprocessor($this->settings);
        }

        return $this->preproccesor->process($string);
    }

    /**
     * {@inheritDoc}
     */
    public function citeText(string $string): string
    {
        if (empty($string)) {
            return '';
        }
        $quoteSymbol = $this->settings->get('quote_symbol');
        $out = '';
        // split already quoted lines
        $citeLines = preg_split(
            "/(^{$quoteSymbol}.*?$\n)/m",
            $string,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        foreach ($citeLines as $citeLine) {
            if (mb_strpos($citeLine, $quoteSymbol) === 0) {
                // already quoted lines need no further processing
                $out .= $citeLine;
                continue;
            }
            // split [bbcode]
            $matches = preg_split(
                '`(\[(.+?)=?.*?\].+?\[/\2\])`',
                $citeLine,
                -1,
                PREG_SPLIT_DELIM_CAPTURE
            );
            $i = 0;
            $line = '';
            foreach ($matches as $match) {
                // the [bbcode] preg_split uses a backreference \2 which is in the $matches
                // but is not needed in the results
                // @td @sm elegant solution
                $i++;
                if ($i % 3 == 0) {
                    continue;
                }
                // wrap long lines
                if (mb_strpos($match, '[') !== 0) {
                    $line .= wordwrap($match);
                } else {
                    $line .= $match;
                }
                // add newline to wrapped lines
                if (mb_strlen($line) > 60) {
                    $out .= $line . "\n";
                    $line = '';
                }
            }
            $out .= $line;
        }
        $out = preg_replace(
            "/^/m",
            $quoteSymbol . " ",
            $out
        );

        return $out;
    }

    /**
     * Get editor
     *
     * @return \Plugin\BbcodeParser\src\Lib\Editor
     */
    protected function getEditor(): Editor
    {
        if (!$this->editor) {
            $this->editor = new Editor();
        }

        return $this->editor;
    }

    /**
     * Reset class
     *
     * @return void
     */
    protected function reset()
    {
        $this->editor = null;
        $this->parser = null;
        $this->preproccesor = null;
    }
}
