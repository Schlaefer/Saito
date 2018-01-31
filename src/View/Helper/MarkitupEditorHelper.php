<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\View;
use Markitup\View\Helper\MarkitupHelper;
use Stopwatch\Lib\Stopwatch;

class MarkitupEditorHelper extends MarkitupHelper
{

    public $helpers = ['Form', 'Html', 'Parser'];

    protected $_nextCssId;

    /**
     * {@inheritDoc}
     */
    public function __construct(View $view, $config = [])
    {
        parent::__construct($view, $config);
        $this->_nextCssId = Configure::read('Saito.markItUp.nextCssId');
    }

    /**
     * Generates markItUp editor buttons based on forum config
     *
     * @param type $id id
     * @return string
     */
    public function getButtonSet($id)
    {
        Stopwatch::start('MarkitupHelper::getButtonSet()');

        $css = '';
        $separator = ['separator' => '&nbsp;'];

        $markitupSet = $this->Parser->getButtonSet();

        foreach ($markitupSet as $key => $code) {
            if ($code === 'separator') {
                $markitupSet[$key] = $separator;
            }
        }

        $this->_buildSmilies($markitupSet, $css);
        $this->_buildAdditionalButtons($markitupSet, $css);
        $markupSet = $this->_convertToJsMarkupSet($markitupSet);
        $script = "markitupSettings = { id: '$id', markupSet: [$markupSet]};";
        $out = $this->Html->scriptBlock($script) .
            "<style type='text/css'>{$css}</style>";

        Stopwatch::stop('MarkitupHelper::getButtonSet()');

        return $out;
    }

    /**
     * convert to markup set
     *
     * @param array $bbcode bbcode
     * @return mixed
     */
    protected function _convertToJsMarkupSet(array $bbcode)
    {
        $markitupSet = [];
        foreach ($bbcode as $set) {
            $markitupSet[] = stripslashes(json_encode($set));
        }
        // markItUp callbacks: start with `function`, don't use `"`
        return preg_replace(
            '/"(function.*?)"/i',
            '\\1',
            implode(",\n", $markitupSet)
        );
    }

    /**
     * build additional buttons
     *
     * @param array $bbcode bbcode
     * @param string $css CSS
     * @return void
     */
    protected function _buildAdditionalButtons(array &$bbcode, &$css)
    {
        $_additionalButtons = Configure::read(
            'Saito.markItUp.additionalButtons'
        );
        if (!empty($_additionalButtons)) {
            foreach ($_additionalButtons as $name => $button) {
                // 'Gacker' => ['name' => 'Gacker', 'replaceWith' => ':gacker:'],
                $bbcode[$name] = [
                    'name' => $button['name'],
                    'title' => $button['title'],
                    'replaceWith' => $button['code'],
                    'className' => 'btn-markItUp-' . $button['title']
                ];
                if (isset($button['icon'])) {
                    $css .= <<<EOF
.markItUp .markItUpButton{$this->_nextCssId} a {
		background-image: url({$this->request->webroot}theme/{$this->theme}/img/markitup/{$button['icon']});
		text-indent: -10000px;
		background-size: 100% 100%;
}
EOF;
                }
                $this->_nextCssId++;
            }
        }
    }

    /**
     * build smilies
     *
     * @param array $bbcode bbcode
     * @param string $css CSS
     * @return void
     */
    protected function _buildSmilies(array &$bbcode, &$css)
    {
        $smilies = $this->_View->get('smiliesData')->get();
        $_smiliesPacked = [];

        $i = 1;
        foreach ($smilies as $smiley) {
            if (isset($_smiliesPacked[$smiley['icon']])) {
                continue;
            }
            $_smiliesPacked[$smiley['icon']] = [
                'className' => "saito-smiley-{$smiley['type']} saito-smiley-{$smiley['icon']}",
                'name' => ''
                /* $smiley['title'] */,
                // additional space to prevent smiley concatenation:
                // `:cry:` and `(-.-)zzZ` becomes `:cry:(-.-)zzZ` which outputs
                // smiley image for `:(`
                'replaceWith' => ' ' . $smiley['code'],
            ];

            if ($smiley['type'] === 'image') {
                $css .= <<<EOF
.markItUp .markItUpButton{$this->_nextCssId}-{$i} a	{
		background-image: url({$this->request->webroot}theme/{$this->theme}/img/smilies/{$smiley['icon']});
}
EOF;
            }
            $i++;
        }
        $this->_nextCssId++;

        $bbcode['Smilies'] = [
            'name' => "<i class='fa fa-s-smile-o'></i>",
            'title' => __('Smilies'),
            'className' => 'btn-markItUp-Smilies',
            'dropMenu' => $_smiliesPacked
        ];
    }

    /**
     * build
     *
     * @param array $settings settings
     * @return array
     */
    protected function _build($settings)
    {
        $default = [
            'set' => 'default',
            'skin' => 'simple',
            'settings' => 'mySettings',
            'parser' => [
                'plugin' => 'markitup',
                'controller' => 'markitup',
                'action' => 'preview',
                'admin' => false,
            ]
        ];
        $settings = array_merge($default, $settings);

        if ($settings['parser']) {
            $settings['parser'] = $this->Html->url(
                Router::url(
                    array_merge($settings['parser'], [$settings['set']])
                )
            );
        }

        /*
         * Saito uses is owne css and sets
         */
        /*
        echo $this->Html->css(array(
            $this->paths['css'] . 'skins' . DS . $settings['skin'] . DS . 'style',
            $this->paths['css'] . 'sets' . DS . $settings['set'] . DS . 'style',
        ), null, array('inline' => true));

        echo $this->Html->script($this->paths['js'] . 'sets' . DS . $settings['set'] . DS . 'set', true);
         *
         */

        return ['settings' => $settings, 'default' => $default];
    }
}
