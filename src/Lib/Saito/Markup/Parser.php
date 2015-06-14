<?php

namespace Saito\Markup;

use Cake\View\Helper;

abstract class Parser
{

    /**
     * @var array cache for app settings
     */
    protected $_cSettings;

    /**
     * @var Helper Helper usually the ParseHelper
     */
    protected $_Helper;

    /**
     * Constructor
     *
     * @param Helper $Helper helper
     * @param array $settings settings
     */
    public function __construct(Helper $Helper, array $settings = [])
    {
        $this->_Helper = $Helper;
        $this->_cSettings = $settings;
    }

    /**
     * should render the markup to HTML
     *
     * @param string $string unescaped markup
     * @param array $options options
     * @return string !!Make sure to escape HTML special chars, or you'll have
     *     a bad day!!
     */
    abstract public function parse($string, array $options = []);

    /**
     * Cite text
     *
     * @param string $string string
     * @return string
     */
    public function citeText($string)
    {
        if (empty($string)) {
            return '';
        }
        $out = '';
        // split already quoted lines
        $citeLines = preg_split(
            "/(^{$this->_cSettings['quote_symbol']}.*?$\n)/m",
            $string,
            null,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        foreach ($citeLines as $citeLine) {
            if (mb_strpos($citeLine, $this->_cSettings['quote_symbol']) === 0) {
                // already quoted lines need no further processing
                $out .= $citeLine;
                continue;
            }
            // split [bbcode]
            $matches = preg_split(
                '`(\[(.+?)=?.*?\].+?\[/\2\])`',
                $citeLine,
                null,
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
            $this->_cSettings['quote_symbol'] . " ",
            $out
        );

        return $out;
    }
}
