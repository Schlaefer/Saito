<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use Cake\View\Helper\HtmlHelper;
use Cake\View\StringTemplateTrait;

/**
 * @property HtmlHelper $Html
 */
class LayoutHelper extends AppHelper
{
    use StringTemplateTrait;

    public $helpers = ['Html', 'Url'];

    protected $_defaultConfig = [
        'templates' => [
            'dropdownMenuDivider' => '<div class="dropdown-divider"></div>',
            'dropdownMenu' => '<div class="dropdown" style="display: inline;">{{button}}<div id="{{id}}" class="dropdown-menu">{{menu}}</div></div>',
        ],
    ];

    /**
     * Generates page heading html
     *
     * @param string $heading heading
     * @param string $tag tag
     * @return string
     */
    public function pageHeading($heading, $tag = 'h1')
    {
        return $this->Html->tag(
            $tag,
            $heading,
            ['class' => 'pageHeading', 'escape' => true]
        );
    }

    /**
     * Generates intoText tag
     *
     * @param string $content content
     * @return string
     */
    public function infoText($content)
    {
        return $this->Html->tag('span', $content, ['class' => 'infoText']);
    }

    /**
     * text with icon
     *
     * @param string $text text
     * @param string $icon icon
     * @return string
     */
    public function textWithIcon($text, $icon): string
    {
        $html = "<i class=\"saito-icon fa fa-{$icon}\"></i>";
        if (!empty($text)) {
                $html .= '&nbsp;<span class="saito-icon-text">' . $text . '</span>';
        }

        return $html;
    }

    /**
     * dropt down menu
     *
     * @param array $menuItems items
     * @param array $options options
     * @return string
     */
    public function dropdownMenuButton(array $menuItems, array $options = [])
    {
        $options += ['class' => 'btn btn-primary'];
        $options['class'] = $options['class'] . ' dropdown-toggle';
        $menu = [];
        foreach ($menuItems as $menuItem) {
            if ($menuItem === 'divider') {
                $menu[] = $this->formatTemplate('dropdownMenuDivider', []);
            } else {
                $menu[] = $menuItem;
            }
        }
        $id = AppHelper::tagId();
        if (!isset($options['title'])) {
            $options['title'] = '<i class="fa fa-wrench"></i>';
        }

        $title = $options['title'];
        unset($options['title']);

        $button = $this->Html->tag(
            'button',
            $title,
            $options + [
                'escape' => false,
                'data-toggle' => 'dropdown',
            ]
        );

        $menu = implode("\n", $menu);

        return $this->formatTemplate('dropdownMenu', [
            'button' => $button,
            'id' => "d$id",
            'menu' => $menu,
        ]);
    }

    /**
     * Creates panel heading HTML-element
     *
     * @param mixed $content content
     * @param array $options options
     *  - `class` string [panel-heading] CSS class for element
     *  - `escape` bool [true] escape output
     *  - `pageHeading` bool [false]
     *  - `tag` string [h2]
     * @return string
     */
    public function panelHeading($content, array $options = [])
    {
        $options += [
            'class' => 'flex-bar-header panel-heading',
            'escape' => true,
            'pageHeading' => false,
            'tag' => 'h2',
        ];
        if ($options['pageHeading']) {
            $options['class'] .= ' pageTitle';
            $options['tag'] = 'h1';
        }
        if (is_string($content)) {
            $content = ['middle' => $content];
        }

        if ($options['escape']) {
            foreach ($content as $k => $v) {
                $content[$k] = h($v);
            }
        }

        $content['middle'] = "<{$options['tag']}>{$content['middle']}</{$options['tag']}>";

        $options['escape'] = false;

        return $this->heading($content, $options);
    }

    /**
     * heading
     *
     * @param mixed $content content
     * @param array $options options
     * @return string
     */
    public function heading($content, array $options = [])
    {
        $options += ['id' => '', 'class' => '', 'escape' => true];
        if (is_string($content)) {
            $contentArray = ['middle' => $content];
        } else {
            $contentArray = $content;
        }
        $contentArray += ['first' => '', 'middle' => '', 'last' => ''];
        $out = '';
        foreach (['first', 'middle', 'last'] as $key) {
            $out .= '<div class="' . $key . '">';
            $out .= $options['escape'] ? h($contentArray[$key]) : $contentArray[$key];
            $out .= '</div>';
        }

        return "<div id=\"{$options['id']}\" class=\"{$options['class']}\">$out</div>";
    }

    /**
     * creates a navigation link for the navigation bar
     *
     * @param string $content link content
     * @param string $url link url
     * @param array $options allows options as HtmlHelper::link
     *    - 'class' a CSS class to apply to the navbar item
     *    - 'position' [left]|center|right
     * @return string navigation link
     */
    public function navbarItem($content, $url, array $options = [])
    {
        $defaults = [
            'class' => 'btn btn-link navbar-item',
            'position' => 'left',
        ];
        $class = '';
        if (isset($options['class'])) {
            $class = $options['class'];
            unset($options['class']);
        }
        $options += $defaults;

        $options['class'] .= " {$options['position']} $class";
        unset($class, $options['position']);

        return $this->Html->link($content, $url, $options);
    }

    /**
     * navbar back
     *
     * @param string $url url
     * @param string $title title
     * @param array $options options
     * @return string
     */
    public function navbarBack($url = null, $title = null, $options = [])
    {
        if ($title === null) {
            if ($url === null) {
                $title = __('back_to_forum_linkname');
            } else {
                $title = __('Back');
            }
        }

        if ($url === null) {
            $url = '/';
        }
        $options += ['escape' => false];
        $content = $this->textWithIcon(h($title), 'arrow-left');

        return $this->navbarItem($content, $url, $options);
    }
}
