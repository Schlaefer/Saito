<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2014-2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Visitors;

use Cake\Core\Configure;
use Cake\View\Helper;
use Saito\Markup\MarkupSettings;
use Saito\Smiley\SmileyRenderer;

/**
 * Class JbbCodeSmileyVisitor replaces ASCII smilies with images
 */
class JbbCodeSmileyVisitor extends JbbCodeTextVisitor
{
    /** @var SmileyRenderer */
    protected $renderer;

    /**
     * {@inheritDoc}
     */
    public function __construct(Helper $Helper, MarkupSettings $_sOptions)
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
