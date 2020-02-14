<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Markup;

use App\View\Helper\ParserHelper;

interface MarkupInterface
{
    /**
     * Constructor
     *
     * @param \Saito\Markup\MarkupSettings $settings forum-settings for markup
     */
    public function __construct(MarkupSettings $settings);

    /**
     * Get editor help.
     *
     * @param \App\View\Helper\ParserHelper $helper ParserHelper
     * @return string HTML-escaped content
     */
    public function getEditorHelp(ParserHelper $helper): string;

    /**
     * Get markup set.
     *
     * @return array
     */
    public function getMarkupSet(): array;

    /**
     * Cite text
     *
     * @param string $string string
     * @return string
     */
    public function citeText(string $string): string;

    /**
     * should render the markup to HTML
     *
     * @param string $string unescaped markup
     * @param \App\View\Helper\ParserHelper $helper ParserHelper
     * @param array $options options
     * @return string !!Make sure to escape HTML special chars, or you'll have
     *     a bad day!!
     */
    public function parse(string $string, ParserHelper $helper, array $options = []): string;

    /**
     * preprocess markup before it's persistently stored
     *
     * @param string $string string
     * @return string
     */
    public function preprocess($string): string;
}
