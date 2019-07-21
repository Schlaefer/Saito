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

use Cake\View\Helper\FormHelper;
use Cake\View\Helper\HtmlHelper;
use Geshi\View\Helper\GeshiHelper;
use SaitoHelp\View\Helper\SaitoHelpHelper;
use Saito\App\Registry;
use Saito\Markup\MarkupInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Parser Helper
 *
 * @property GeshiHelper $Geshi
 * @property FormHelper $Form
 * @property HtmlHelper $Html
 * @property SaitoHelpHelper $SaitoHelp
 */
class ParserHelper extends AppHelper
{

    /**
     * @var array these Helpers are also used in the Parser
     */
    public $helpers = [
        'MailObfuscator.MailObfuscator',
        'Geshi.Geshi',
        'Form',
        'Html',
        'Text',
        'Url',
        //= usefull in Parsers
        'Layout',
        'SaitoHelp'
    ];

    /**
     * @var array parserCache for parsed markup
     *
     * Esp. useful for repeating signatures in long mix view threads
     */
    protected $_parserCache = [];

    /** @var MarkupInterface */
    protected $Markup;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        /** @var MarkupInterface */
        $Markup = Registry::get('Markup');
        $this->Markup = $Markup;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender($viewFile)
    {
        if (isset($this->request) && $this->request->getParam('action') === 'preview') {
            $this->Geshi->showPlainTextButton = false;
        }
    }

    /**
     * cite text
     *
     * @param string $string string
     * @return string
     */
    public function citeText($string)
    {
        return $this->Markup->citeText($string);
    }

    /**
     * get editor help
     *
     * @return mixed
     */
    public function editorHelp()
    {
        return $this->Markup->getEditorHelp($this);
    }

    /**
     * Get HTML for text editor
     *
     * @param string $field model-field
     * @return string HTML
     */
    public function editor(string $field): string
    {
        ['buttons' => $buttons, 'smilies' => $smilies] = $this->getButtonSet();
        $editor = $this->Form->textarea(
            $field,
            [
                'class' => 'form-control',
                'data-buttons' => json_encode($buttons),
                'data-smilies' => json_encode($smilies),
                'tabindex' => 3
            ]
        );

        return $this->Html->div('js-editor form-group', $editor);
    }

    /**
     * get button set
     *
     * @return mixed
     */
    private function getButtonSet()
    {
        $buttons = $this->Markup->getMarkupSet();
        $smilies = $this->_View->get('smiliesData')->get();

        if (!empty($smilies)) {
            $buttons[] = [
                'type' => 'separator'
            ];
            $buttons[] = [
                'name' => "<i class='fa fa-s-smile-o'></i>",
                'title' => __('Smilies'),
                'className' => 'btn-markup-Smilies',
                'type' => 'saito-smilies',
                'handler' => 'smilies',
            ];
        }

        return compact('buttons', 'smilies');
    }

    /**
     * parse
     *
     * @param string $string string
     * @param array $options options
     * @return string
     */
    public function parse($string, array $options = [])
    {
        Stopwatch::start('ParseHelper::parse()');
        if (empty($string) || $string === 'n/t') {
            Stopwatch::stop('ParseHelper::parse()');

            return $string;
        }

        $defaults = ['return' => 'html', 'embed' => true, 'multimedia' => true, 'wrap' => true];
        $options += $defaults;

        $cacheId = md5(serialize($options) . $string);
        if (isset($this->_parserCache[$cacheId])) {
            $html = $this->_parserCache[$cacheId];
        } else {
            $html = $this->Markup->parse($string, $this, $options);
            $this->_parserCache[$cacheId] = $html;
        }
        if ($options['return'] === 'html' && $options['wrap']) {
            $html = '<div class="richtext">' . $html . '</div>';
        }
        Stopwatch::stop('ParseHelper::parse()');

        return $html;
    }
}
