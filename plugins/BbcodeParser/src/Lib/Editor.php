<?php

namespace Plugin\BbcodeParser\src\Lib;

class Editor extends \Saito\Markup\Editor
{
    /**
     * {@inheritDoc}
     */
    public function getEditorHelp()
    {
        return $this->_Helper->SaitoHelp->icon(
            'BbcodeParser.1',
            ['label' => __d('bbcode_parser', 'parsedAsBbcode')]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getMarkupSet()
    {
        return [
            'Bold' => [
                'name' => "<i class='fa fa-bold'></i>",
                'title' => __('Bold'),
                'className' => 'btn-markItUp-Bold',
                'key' => 'B',
                'openWith' => '[b]',
                'closeWith' => '[/b]'
            ],
            'Italic' => [
                'name' => "<i class='fa fa-italic'></i>",
                'title' => __('Italic'),
                'className' => 'btn-markItUp-Italic',
                'key' => 'I',
                'openWith' => '[i]',
                'closeWith' => '[/i]'
            ],
            'Stroke' => [
                'name' => "<i class='fa fa-strikethrough'></i>",
                'title' => __('Strike Through'),
                'className' => 'btn-markItUp-Stroke',
                'openWith' => '[strike]',
                'closeWith' => '[/strike]'
            ],
            'Code' => [
                'name' => "<i class='fa fa-s-code'></i>",
                'title' => __('Code'),
                'className' => 'btn-markItUp-Code',
                'openWith' => '[code=text]\n',
                'closeWith' => '\n[/code]'
            ],
            'Bulleted list' => [
                'name' => "<i class='fa fa-list-ul'></i>",
                'title' => __('Bullet List'),
                'className' => 'btn-markItUp-List',
                'openWith' => '[list]\n[*] ',
                'closeWith' => '\n[*]\n[/list]'
            ],
            'Spoiler' => [
                'name' => "<i class='fa fa-stop'></i>",
                'className' => 'btn-markItUp-Spoiler',
                'title' => __('Spoiler'),
                'openWith' => '[spoiler]',
                'closeWith' => '[/spoiler]'
            ],
            'Quote' => [
                'name' => "<i class='fa fa-quote-left'></i>",
                'className' => 'btn-markItUp-Quote',
                'title' => __('Cite'),
                'openWith' => '[quote]',
                'closeWith' => '[/quote]'
            ],
            'separator',
            'Link' => [
                'name' => "<i class='fa fa-link'></i>",
                'title' => __('Link'),
                'className' => 'btn-markItUp-Link',
                'key' => 'L',
                'openWith' =>
                    '[url=[![' . __('geshi_link_popup') . ']!]]',
                'closeWith' => '[/url]',
                'placeHolder' => __('geshi_link_placeholder'),
            ],
            'Media' => [
                'name' => "<i class='fa fa-multimedia'></i>",
                'className' => 'btn-markItUp-Media',
                'title' => __('Media'),
                'key' => 'P',
            ],
            'Upload' => [
                'name' => '<i class=\'fa fa-upload\'></i>',
                'title' => __('Upload'),
                'className' => 'btn-markItUp-Upload'
            ],
            'separator'
        ];
    }
}
