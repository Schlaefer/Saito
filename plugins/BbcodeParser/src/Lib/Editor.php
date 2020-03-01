<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace BbcodeParser\Lib;

use App\View\Helper\ParserHelper;

class Editor
{
    /**
     * {@inheritDoc}
     */
    public function getEditorHelp(ParserHelper $helper): string
    {
        return $helper->SaitoHelp->icon(
            'BbcodeParser.1',
            ['label' => __d('bbcode_parser', 'parsedAsBbcode')]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getMarkupSet(): array
    {
        return [
            [
                'name' => "<i class='fa fa-bold'></i>",
                'title' => __('Bold'),
                'className' => 'btn-markup-Bold',
                'type' => 'enclose',
                'openWith' => '[b]',
                'closeWith' => '[/b]',
            ],
            [
                'name' => "<i class='fa fa-italic'></i>",
                'title' => __('Italic'),
                'className' => 'btn-markup-Italic',
                'type' => 'enclose',
                'openWith' => '[i]',
                'closeWith' => '[/i]',
            ],
            [
                'name' => "<i class='fa fa-strikethrough'></i>",
                'title' => __('Strike Through'),
                'className' => 'btn-markup-Stroke',
                'type' => 'enclose',
                'openWith' => '[strike]',
                'closeWith' => '[/strike]',
            ],
            [
                'name' => "<i class='fa fa-s-code'></i>",
                'title' => __('Code'),
                'className' => 'btn-markup-Code',
                'type' => 'enclose',
                'openWith' => "[code=text]\n",
                'closeWith' => "\n[/code]",
            ],
            [
                'name' => "<i class='fa fa-list-ul'></i>",
                'title' => __('Bullet List'),
                'className' => 'btn-markup-List',
                'type' => 'enclose',
                'openWith' => "[list]\n[*] ",
                'closeWith' => "\n[*]\n[/list]",
            ],
            [
                'name' => "<i class='fa fa-stop'></i>",
                'className' => 'btn-markup-Spoiler',
                'title' => __('Spoiler'),
                'type' => 'enclose',
                'openWith' => '[spoiler]',
                'closeWith' => '[/spoiler]',
            ],
            [
                'name' => "<i class='fa fa-quote-left'></i>",
                'className' => 'btn-markup-Quote',
                'title' => __('Cite'),
                'type' => 'enclose',
                'openWith' => '[quote]',
                'closeWith' => '[/quote]',
            ],
            [
                'type' => 'separator',
            ],
            [
                'name' => "<i class='fa fa-link'></i>",
                'title' => __('Link'),
                'className' => 'btn-markup-Link',
                'type' => 'saito-link',
                'handler' => 'link',
            ],
            [
                'name' => "<i class='fa fa-multimedia'></i>",
                'className' => 'btn-markup-Media',
                'title' => __('Media'),
                'type' => 'saito-media',
                'handler' => 'media',
            ],
            [
                'name' => '<i class=\'fa fa-upload\'></i>',
                'title' => __('Upload'),
                'className' => 'btn-markup-Upload',
                'type' => 'saito-upload',
                'handler' => 'upload',
            ],
        ];
    }
}
