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

use App\View\Helper\ParserHelper;
use Saito\Markup\MarkupSettings;
use Saito\Smiley\SmileyRenderer;

/**
 * Class JbbCodeSmileyVisitor replaces ASCII smilies with images
 */
class JbbCodeSmileyVisitor extends JbbCodeTextVisitor
{
    /**
     * @var \Saito\Smiley\SmileyRenderer
     */
    protected $renderer;

    /**
     * {@inheritDoc}
     */
    public function __construct(ParserHelper $Helper, MarkupSettings $_sOptions)
    {
        parent::__construct($Helper, $_sOptions);

        $smiliesData = $Helper->getView()->get('smiliesData');
        $this->renderer = (new SmileyRenderer($smiliesData))
            ->setHelper($Helper->Html);
    }

    /**
     * {@inheritDoc}
     */
    protected function _processTextNode($string, $node)
    {
        return $this->renderer->replace($string);
    }
}
