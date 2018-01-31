<?php

namespace Plugin\BbcodeParser\src\Lib;

use Cake\Core\Plugin;
use Plugin\BbcodeParser\src\Lib\jBBCode\Definitions;
use Plugin\BbcodeParser\src\Lib\jBBCode\Visitors;
use Plugin\BbcodeParser\src\Lib\Processors;

class Parser extends \Saito\Markup\Parser
{

    /**
     * @var \JBBCode\Parser
     */
    protected $_Parser;

    protected $_Preprocessors;

    protected $_Postprocessors;

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
                'replacement' => '<strong>{param}</strong>'
            ],
            // code
            'codeWithAttributes' => [
                'type' => 'class',
                'title' => 'CodeWithAttributes'
            ],
            'codeWithoutAttributes' => [
                'type' => 'class',
                'title' => 'CodeWithoutAttributes'
            ],
            // edit marker
            'e' => [
                'type' => 'replacement',
                'title' => 'e',
                'replacement' => '<span class="richtext-editMark"></span>{param}'
            ],
            // float
            'float' => [
                'type' => 'replacement',
                'title' => 'float',
                'replacement' => '<div class="clearfix"></div><div class="richtext-float">{param}</div>'
            ],
            // email
            'email' => [
                'type' => 'class',
                'title' => 'email'
            ],
            'emailWithAttributes' => [
                'type' => 'class',
                'title' => 'emailWithAttributes'
            ],
            // hr
            'hr' => [
                'type' => 'replacement',
                'title' => 'hr',
                'replacement' => '<hr>{param}'
            ],
            '---' => [
                'type' => 'replacement',
                'title' => '---',
                'replacement' => '<hr>{param}'
            ],
            // emphasis
            'i' => [
                'type' => 'replacement',
                'title' => 'i',
                'replacement' => '<em>{param}</em>'
            ],
            // list
            'list' => [
                'type' => 'class',
                'title' => 'ulList'
            ],
            // spoiler
            'spoiler' => [
                'type' => 'class',
                'title' => 'spoiler'
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
                'title' => 'link'
            ],
            'linkWithAttributes' => [
                'type' => 'class',
                'title' => 'linkWithAttributes'
            ],
            'url' => [
                'type' => 'class',
                'title' => 'url'
            ],
            'urlWithAttributes' => [
                'type' => 'class',
                'title' => 'urlWithAttributes'
            ],
            // quotes
            'quote' => [
                'type' => 'replacement',
                'title' => 'quote',
                'replacement' => '<blockquote>{param}</blockquote>'
            ]
        ],
        'multimedia' => [
            'embed' => [
                'type' => 'class',
                'title' => 'Embed'
            ],
            'flash' => [
                'type' => 'class',
                'title' => 'Flash'
            ],
            'iframe' => [
                'type' => 'class',
                'title' => 'Iframe'
            ],
            'image' => [
                'type' => 'class',
                'title' => 'Image'
            ],
            'imageWithAttributes' => [
                'type' => 'class',
                'title' => 'ImageWithAttributes'
            ],
            'html5audio' => [
                'type' => 'class',
                'title' => 'Html5Audio'
            ],
            'html5video' => [
                'type' => 'class',
                'title' => 'Html5Video'
            ],
            'upload' => [
                'type' => 'class',
                'title' => 'Upload'
            ],
            'uploadWithAttributes' => [
                'type' => 'class',
                'title' => 'UploadWithAttributes'
            ]
        ]
    ];

    /**
     * Initialized parsers
     *
     * @var array
     */
    protected $_initializedParsers = [];

    /**
     * Parses BBCode
     *
     * @param string $string string to parse
     * @param array $options options
     * - `return` string "html"|"text" result type
     * - `multimedia` bool true|false parse or ignore multimedia content
     *
     * @return mixed|string
     */
    public function parse($string, array $options = [])
    {
        $this->_initParser($options);

        $string = $this->_Preprocessors->process($string);

        $this->_Parser->parse($string);

        $this->_Parser->accept(
            new Visitors\JbbCodeNl2BrVisitor($this->_Helper, $options)
        );
        if ($this->_cSettings['autolink']) {
            $this->_Parser->accept(
                new Visitors\JbbCodeAutolinkVisitor($this->_Helper, $options)
            );
        }
        if ($this->_cSettings['smilies']) {
            $this->_Parser->accept(
                new Visitors\JbbCodeSmileyVisitor($this->_Helper, $options)
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
     * @param array $options options
     *
     * @return void
     * @throws \Exception
     */
    protected function _initParser(&$options)
    {
        $options = array_merge($this->_cSettings, $options);

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
        $this->_addDefinitionSet('basic', $options);

        if (!empty($this->_cSettings['bbcode_img']) && $options['multimedia']) {
            $this->_addDefinitionSet('multimedia', $options);
        }

        $this->_Preprocessors = new Processors\BbcodeProcessorCollection();
        $this->_Preprocessors->add(new Processors\BbcodePreparePreprocessor());
        $this->_Postprocessors = new Processors\BbcodeProcessorCollection();
        $this->_Postprocessors->add(new Processors\BbcodeQuotePostprocessor($options));

        $this->_initializedParsers[$parserId] = $this->_Parser;
    }

    /**
     * Add definitin set
     *
     * @param string $set set
     * @param array $options options
     *
     * @return void
     * @throws \Exception
     */
    protected function _addDefinitionSet($set, $options)
    {
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
                    $folder = Plugin::path('BbcodeParser') . DS . 'src' . DS . 'Lib' . DS . 'jBBCode' . DS . 'Definitions' . DS;
                    require_once $folder . 'JbbCodeDefinitions.php';
                    require_once $folder . 'JbbHtml5MediaCodeDefinition.php';
                    require_once $folder . 'JbbCodeCodeDefinition.php';
                    $class = '\Plugin\BbcodeParser\src\Lib\jBBCode\Definitions\\' . ucfirst($title);
                    $this->_Parser->addCodeDefinition(new $class($this->_Helper, $options));
                    break;
                default:
                    throw new \Exception();
            }
        }
    }
}
