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

use Cake\View\Helper;
use BbcodeParser\Lib\jBBCode\Visitors;
use Saito\Markup\MarkupSettings;

class Parser
{
    /**
     * @var \JBBCode\Parser
     */
    protected $_Parser;

    protected $_Preprocessors;

    protected $_Postprocessors;

    /**
     * @var bool
     */
    private $areCombinedClassFilesLoaded = false;

    /**
     * @var array
     *
     * [
     *    <set title> => [
     *      <arbitrary definition title> => [ <definition> ]
     *    ]
     * ]
     *
     */
    protected $_tags = [
        'basic' => [
            // strong
            'b' => [
                'type' => 'replacement',
                'title' => 'b',
                'replacement' => '<strong>{param}</strong>',
            ],
            // code
            'codeWithAttributes' => [
                'type' => 'class',
                'title' => 'CodeWithAttributes',
            ],
            'codeWithoutAttributes' => [
                'type' => 'class',
                'title' => 'CodeWithoutAttributes',
            ],
            // edit marker
            'e' => [
                'type' => 'replacement',
                'title' => 'e',
                'replacement' => '<span class="richtext-editMark"></span>{param}',
            ],
            // float
            'float' => [
                'type' => 'replacement',
                'title' => 'float',
                'replacement' => '<div class="clearfix"></div><div class="richtext-float">{param}</div>',
            ],
            // email
            'email' => [
                'type' => 'class',
                'title' => 'email',
            ],
            'emailWithAttributes' => [
                'type' => 'class',
                'title' => 'emailWithAttributes',
            ],
            // hr
            'hr' => [
                'type' => 'replacement',
                'title' => 'hr',
                'replacement' => '<hr>{param}',
            ],
            '---' => [
                'type' => 'replacement',
                'title' => '---',
                'replacement' => '<hr>{param}',
            ],
            // emphasis
            'i' => [
                'type' => 'replacement',
                'title' => 'i',
                'replacement' => '<em>{param}</em>',
            ],
            // list
            'list' => [
                'type' => 'class',
                'title' => 'ulList',
            ],
            // spoiler
            'spoiler' => [
                'type' => 'class',
                'title' => 'spoiler',
            ],
            // strike
            's' => [
                'type' => 'replacement',
                'title' => 's',
                'replacement' => '<del>{param}</del>',
            ],
            'strike' => [
                'type' => 'replacement',
                'title' => 'strike',
                'replacement' => '<del>{param}</del>',
            ],
            // url
            'link' => [
                'type' => 'class',
                'title' => 'link',
            ],
            'linkWithAttributes' => [
                'type' => 'class',
                'title' => 'linkWithAttributes',
            ],
            'url' => [
                'type' => 'class',
                'title' => 'url',
            ],
            'urlWithAttributes' => [
                'type' => 'class',
                'title' => 'urlWithAttributes',
            ],
            // quotes
            'quote' => [
                'type' => 'replacement',
                'title' => 'quote',
                'replacement' => '<blockquote>{param}</blockquote>',
            ],
        ],
        'multimedia' => [
            'image' => [
                'type' => 'class',
                'title' => 'Image',
            ],
            'imageWithAttributes' => [
                'type' => 'class',
                'title' => 'ImageWithAttributes',
            ],
            'html5audio' => [
                'type' => 'class',
                'title' => 'Html5Audio',
            ],
            'html5audioWithAttributes' => [
                'type' => 'class',
                'title' => 'Html5AudioWithAttributes',
            ],
            'html5video' => [
                'type' => 'class',
                'title' => 'Html5Video',
            ],
            'html5videoWithAttributes' => [
                'type' => 'class',
                'title' => 'Html5VideoWithAttributes',
            ],
            'upload' => [
                'type' => 'class',
                'title' => 'Upload',
            ],
            'uploadWithAttributes' => [
                'type' => 'class',
                'title' => 'UploadWithAttributes',
            ],
            'fileWithAttributes' => [
                'type' => 'class',
                'title' => 'fileWithAttributes',
            ],
        ],
        'embed' => [
            'embed' => [
                'type' => 'class',
                'title' => 'Embed',
            ],
            'flash' => [
                'type' => 'class',
                'title' => 'Flash',
            ],
            'iframe' => [
                'type' => 'class',
                'title' => 'Iframe',
            ],
        ],
    ];

    /**
     * Initialized parsers
     *
     * @var array
     */
    protected $_initializedParsers = [];

    /**
     * @var \Saito\Markup\MarkupSettings cache for app settings
     */
    protected $_cSettings;

    /**
     * @var \Cake\View\Helper Helper usually the ParseHelper
     */
    protected $_Helper;

    /**
     * Constructor
     *
     * @param \Cake\View\Helper $Helper helper
     * @param \Saito\Markup\MarkupSettings $settings settings
     */
    public function __construct(Helper $Helper, MarkupSettings $settings)
    {
        $this->_Helper = $Helper;
        $this->_cSettings = $settings;
    }

    /**
     * Parses BBCode
     *
     * @param string $string string to parse
     * @param array $options options
     * - `return` string "html"|"text" result type
     * - `multimedia` bool true|false parse or ignore multimedia content
     * - `embed` bool true|false parse or ignore embed content
     *
     * @return mixed|string
     */
    public function parse($string, array $options = [])
    {
        $options += [
            'embed' => true,
            'multimedia' => true,
            'return' => 'html',
        ];

        $this->_initParser($options);

        $string = $this->_Preprocessors->process($string);

        $this->_Parser->parse($string);

        $this->_Parser->accept(
            new Visitors\JbbCodeNl2BrVisitor($this->_Helper, $this->_cSettings)
        );
        if ($this->_cSettings->get('autolink')) {
            $this->_Parser->accept(
                new Visitors\JbbCodeAutolinkVisitor($this->_Helper, $this->_cSettings)
            );
        }
        if ($this->_cSettings->get('smilies')) {
            $this->_Parser->accept(
                new Visitors\JbbCodeSmileyVisitor($this->_Helper, $this->_cSettings)
            );
        }

        switch ($options['return']) {
            case 'text':
                $html = $this->_Parser->getAsText();
                break;
            default:
                $html = $this->_Parser->getAsHtml();
        }

        $html = $this->_Postprocessors->process($html);

        return $html;
    }

    /**
     * Init parser
     *
     * @param array $options merged MarkupSettings and parse-run-option
     *
     * @return void
     * @throws \Exception
     */
    protected function _initParser($options)
    {
        // serializing complex objects kills PHP
        $serializable = array_filter(
            $options,
            function ($value) {
                return !is_object($value);
            }
        );
        $parserId = md5(serialize($serializable));
        if (isset($this->_initializedParsers[$parserId])) {
            $this->_Parser = $this->_initializedParsers[$parserId];

            return;
        }

        $this->_Parser = new \JBBCode\Parser();
        $this->_addDefinitionSet('basic', $this->_cSettings);

        if ($this->_cSettings->get('bbcode_img') && $options['multimedia']) {
            $this->_addDefinitionSet('multimedia', $this->_cSettings);
        }

        if ($this->_cSettings->get('bbcode_img') && $options['embed']) {
            $this->_addDefinitionSet('embed', $this->_cSettings);
        }

        $this->_Preprocessors = new Processors\BbcodeProcessorCollection();
        $this->_Preprocessors->add(new Processors\BbcodePreparePreprocessor($this->_cSettings));
        $this->_Postprocessors = new Processors\BbcodeProcessorCollection();
        $this->_Postprocessors->add(new Processors\BbcodeQuotePostprocessor($this->_cSettings));

        $this->_initializedParsers[$parserId] = $this->_Parser;
    }

    /**
     * Add definitin set
     *
     * @param string $set set
     * @param \Saito\Markup\MarkupSettings $options options
     *
     * @return void
     * @throws \Exception
     */
    protected function _addDefinitionSet($set, MarkupSettings $options)
    {
        $this->loadCombinedClassFiles();

        foreach ($this->_tags[$set] as $definition) {
            $title = $definition['title'];
            switch ($definition['type']) {
                case 'replacement':
                    $builder = new \JBBCode\CodeDefinitionBuilder(
                        $title,
                        $definition['replacement']
                    );
                    $this->_Parser->addCodeDefinition($builder->build());
                    break;
                case 'class':
                    $class = '\BbcodeParser\Lib\jBBCode\Definitions\\' . ucfirst($title);
                    $this->_Parser->addCodeDefinition(new $class($this->_Helper, $options));
                    break;
                default:
                    throw new \Exception();
            }
        }
    }

    /**
     * Class combined definition class files before first usage
     *
     * @return void
     */
    protected function loadCombinedClassFiles()
    {
        if ($this->areCombinedClassFilesLoaded) {
            return;
        }

        $folder = __DIR__ . '/jBBCode/Definitions/';
        require_once $folder . 'JbbCodeDefinitions.php';
        require_once $folder . 'JbbHtml5MediaCodeDefinition.php';
        require_once $folder . 'JbbCodeCodeDefinition.php';

        $this->areCombinedClassFilesLoaded = true;
    }
}
