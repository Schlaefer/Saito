<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use App\View\Helper\ParserHelper;
use Cake\Core\Configure;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Markitup\View\Helper\MarkitupHelper;
use Stopwatch\Lib\Stopwatch;

/**
 * Markitup Editor Helper
 *
 * @property HtmlHelper $Html
 * @property ParserHelper $Parser
 */
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

        $out = '';
        $css = '';
        $separator = ['separator' => '&nbsp;'];

        $markitupSet = $this->Parser->getButtonSet();

        foreach ($markitupSet as $key => $code) {
            if ($code === 'separator') {
                $markitupSet[$key] = $separator;
            }
        }

        $smiliesData = $this->_buildSmilies($markitupSet);
        if ($smiliesData) {
            $script = 'smiliesData = ' . json_encode($smiliesData) . ';';
            $out .= $this->Html->scriptBlock($script);
        }

        $markupSet = $this->_convertToJsMarkupSet($markitupSet);
        $script = "markitupSettings = { id: '$id', markupSet: [$markupSet]};";
        $out .= $this->Html->scriptBlock($script) .
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
     * Build smilies into an array
     *
     * @param array $bbcode bbcode
     * @return null|array smilies data
     */
    protected function _buildSmilies(array &$bbcode): ?array
    {
        $smilies = $this->_View->get('smiliesData')->get();

        if (empty($smilies)) {
            return null;
        }

        $bbcode['Smilies'] = [
            'name' => "<i class='fa fa-s-smile-o'></i>",
            'title' => __('Smilies'),
            'className' => 'btn-markItUp-Smilies',
        ];

        return $smilies;
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
