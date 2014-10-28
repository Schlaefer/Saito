<?php

	App::uses('SaitoMarkupParser', 'Lib/Saito/Markup');
	include CakePlugin::path('BbcodeParser') . DS . 'Lib' . DS . 'jBBCode' . DS . 'Definitions' . DS . 'JbbCodeDefinitions.php';

	App::uses('JbbCodeNl2BrVisitor', 'BbcodeParser.Lib/jBBCode/Visitors');
	App::uses('JbbCodeAutolinkVisitor', 'BbcodeParser.Lib/jBBCode/Visitors');
	App::uses('JbbCodeSmileyVisitor', 'BbcodeParser.Lib/jBBCode/Visitors');

	App::uses('BbcodeProcessorCollection', 'BbcodeParser.Lib/Processors');
	App::uses('BbcodeImageUploadLegacyPreprocessor', 'BbcodeParser.Lib/Processors');
	App::uses('BbcodePreparePreprocessor', 'BbcodeParser.Lib/Processors');
	App::uses('BbcodeQuotePostprocessor', 'BbcodeParser.Lib/Processors');

	class BbcodeParser extends SaitoMarkupParser {

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
					'replacement' => '<div class="richtext-float">{param}</div>'
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
		protected $_initializedParsers = array();

		/**
		 * Parses BBCode
		 *
		 * ### Options
		 *
		 * - `return` string "html"|"text" result type
		 * - `multimedia` bool true|false parse or ignore multimedia content
		 *
		 * @param $string
		 * @param array $options
		 * @return mixed|string
		 */
		public function parse($string, array $options = []) {
			$this->_initParser($options);

			$string = $this->_Preprocessors->process($string);

			$this->_Parser->parse($string);

			$this->_Parser->accept(new JbbCodeNl2BrVisitor($this->_Helper, $options));
			if ($this->_cSettings['autolink']) {
				$this->_Parser->accept(new JbbCodeAutolinkVisitor($this->_Helper, $options));
			}
			if ($this->_cSettings['smilies']) {
				$this->_Parser->accept(new JbbCodeSmileyVisitor($this->_Helper, $options));
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

		protected function _initParser(&$options) {
			$options = array_merge($this->_cSettings, $options);

			// serializing complex objects kills PHP
			$serializable = array_filter($this->_cSettings, function ($value) {
				return !is_object($value);
			});
			$parserId = md5(serialize($serializable));
			if (isset($this->_initializedParsers[$parserId])) {
				$this->_Parser = $this->_initializedParsers[$parserId];
				return;
			}

			$this->_Parser = new JBBCode\Parser();
			$this->_addDefinitionSet('basic', $options);

			if (!empty($this->_cSettings['bbcode_img']) && $options['multimedia']) {
				$this->_addDefinitionSet('multimedia', $options);
			}

			$this->_Preprocessors = new BbcodeProcessorCollection();
			$this->_Preprocessors->add(new BbcodeImageUploadLegacyPreprocessor());
			$this->_Preprocessors->add(new BbcodePreparePreprocessor());

			$this->_Postprocessors = new BbcodeProcessorCollection();
			$this->_Postprocessors->add(new BbcodeQuotePostprocessor($options));

			$this->_initializedParsers[$parserId] = $this->_Parser;
		}

		/**
		 * @param $set
		 * @param $options
		 * @throws Exception
		 */
		protected function _addDefinitionSet($set, $options) {
			foreach ($this->_tags[$set] as $definition) {
				$title = $definition['title'];
				switch ($definition['type']) {
					case 'replacement':
						$builder = new JBBCode\CodeDefinitionBuilder($title,
							$definition['replacement']);
						$this->_Parser->addCodeDefinition($builder->build());
						break;
					case 'class':
						$class = '\Saito\Jbb\CodeDefinition\\' . ucfirst($title);
						$this->_Parser->addCodeDefinition(new $class($this->_Helper, $options));
						break;
					default:
						throw new Exception();
				}
			}
		}

	}

