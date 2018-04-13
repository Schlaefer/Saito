<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

class LayoutHelper extends AppHelper
{

    public $helpers = ['Html', 'Url'];

    /**
     * {@inheritDoc}
     */
    public function beforeLayout($layoutFile)
    {
        $this->_includeCakeDebugAssets();
    }

    /**
     * jquery tag
     *
     * @return string
     */
    public function jQueryTag()
    {
        $url = 'dist/';
        $name = 'jquery';
        if ((int)Configure::read('debug') === 0) {
            $name = $name . '.min';
        }

        return $this->Html->script(
            $this->Url->assetUrl(
                $url . $name,
                ['ext' => '.js', 'fullBase' => true]
            )
        );
    }

    /**
     * Include CakePHP assets required for debugging
     *
     * @return void
     */
    protected function _includeCakeDebugAssets()
    {
        if (!Configure::read('debug')) {
            return;
        }
        $stylesheets = ['stylesheets/cake.css'];
        $this->Html->css($stylesheets, ['block' => 'css']);
    }

    /**
     * Output link to Android touch icon
     *
     * @param mixed $size size
     * @param array $options options
     * @return string
     */
    public function androidTouchIcon($size, array $options = [])
    {
        return $this->_touchIcon(
            $size,
            $options + ['rel' => 'shortcut icon']
        );
    }

    /**
     * Output link to iOS touch icon
     *
     * @param mixed $size size
     * @param array $options options
     * @return string
     */
    public function appleTouchIcon($size, array $options = [])
    {
        return $this->_touchIcon($size, $options);
    }

    /**
     * Output link to touch icon
     *
     * Files must be placed in <theme>/webroot/img/<baseName>-<size>x<size>.png
     *
     * @param mixed $size integer or array with integer
     * @param array $options options
     *  `baseName` (default: 'app-icon')
     *  `precomposed` adds '-precomposed' to baseName (default: false)
     *  `rel` (default: 'apple-touch-icon')
     *  `size` outputs "size"-attribute (default: true)
     * @return string
     */
    protected function _touchIcon($size, array $options = [])
    {
        if (is_array($size)) {
            $out = '';
            foreach ($size as $s) {
                $out .= $this->appleTouchIcon($s, $options);
            }

            return $out;
        }

        $defaults = [
            'baseName' => 'app-icon',
            'precomposed' => false,
            'rel' => 'apple-touch-icon',
            'size' => true
        ];
        $options += $defaults;

        $xSize = "{$size}x{$size}";

        $basename = $options['baseName'];
        if ($options['precomposed']) {
            $basename .= '-precomposed';
        }
        $filename = "{$basename}-{$xSize}.png";

        $url = $this->Url->assetUrl(
            $this->theme . '.' . Configure::read('App.imageBaseUrl'),
            ['fullBase' => true]
        );
        $url = "{$url}{$filename}";

        $out = "<link rel=\"{$options['rel']}\" ";
        if ($options['size']) {
            $out .= "size=\"{$xSize}\" ";
        }
        $out .= "href=\"{$url}\">";

        return $out;
    }

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
    public function textWithIcon($text, $icon)
    {
        return <<<EOF
				<i class="saito-icon fa fa-$icon"></i>
				<span class="saito-icon-text">$text</span>
EOF;
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
        $options += ['class' => ''];
        $divider = '<li class="dropdown-divider"></li>';
        $menu = '';
        foreach ($menuItems as $menuItem) {
            if ($menuItem === 'divider') {
                $menu .= $divider;
            } else {
                $menu .= "<li>$menuItem</li>";
            }
        }
        $id = AppHelper::tagId();
        if (!isset($options['title'])) {
            $options['title'] = '<i class="fa fa-wrench"></i>&nbsp;<i class="fa fa-caret-down"></i>';
        }

        $title = $options['title'];
        unset($options['title']);

        $button = $this->Html->tag(
            'button',
            $title,
            $options + [
                'escape' => false,
                'onclick' => "$(this).dropdown('attach', '#d$id');"
            ]
        );
        $out = <<<EOF
				$button
				<div id="d$id" class="dropdown-relative dropdown dropdown-tip">
					<ul class="dropdown-menu">
							$menu
					</ul>
				</div>
EOF;

        return $out;
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
            'class' => 'panel-heading',
            'escape' => true,
            'pageHeading' => false,
            'tag' => 'h2'
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
        $options += ['class' => '', 'escape' => true];
        if (is_string($content)) {
            $contentArray = ['middle' => $content];
        } else {
            $contentArray = $content;
        }
        $contentArray += ['first' => '', 'middle' => '', 'last' => ''];
        $out = '';
        foreach (['first', 'middle', 'last'] as $key) {
            $out .= '<div class="heading-3-' . $key . '">';
            $out .= $options['escape'] ? h($contentArray[$key]) : $contentArray[$key];
            $out .= '</div>';
        }

        return "<div class=\"{$options['class']} heading-3\">$out</div>";
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
            'class' => 'navbar-item',
            'position' => 'left'
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
