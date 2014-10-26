<?php

	App::uses('AppHelper', 'View/Helper');
	App::uses('MarkupParserInterface', 'Lib/Bbcode');
	include APPLIBS . 'Bbcode' . DS . 'jBBCode' . DS . 'Definitions' . DS . 'JbbCodeDefinitions.php';

	App::uses('JbbCodeNl2BrVisitor', 'Lib/Bbcode/jBBCode/Visitors');
	App::uses('JbbCodeAutolinkVisitor', 'Lib/Bbcode/jBBCode/Visitors');
	App::uses('JbbCodeSmileyVisitor', 'Lib/Bbcode/jBBCode/Visitors');

	App::uses('BbcodeProcessorCollection', 'Lib/Bbcode/Processors');
	App::uses('BbcodeImageUploadLegacyPreprocessor', 'Lib/Bbcode/Processors');
	App::uses('BbcodePreparePreprocessor', 'Lib/Bbcode/Processors');
	App::uses('BbcodeQuotePostprocessor', 'Lib/Bbcode/Processors');

	/**
	 * Class BbcodeHelper
	 */
	class BbcodeHelper extends AppHelper implements MarkupParserInterface {

		/**
		 * @var array cache for rendered BBCode
		 *
		 * Esp. useful for repeating signatures in long mix view threads
		 */
		protected $_cache = [];

		/**
		 * @var array cache for app settings
		 */
		protected $_cSettings;

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
					'replacement' => '<span class="c-bbcode-edit"></span>{param}'
				],
				// float
				'float' => [
					'type' => 'replacement',
					'title' => 'float',
					'replacement' => '<div class="c-bbcode-float">{param}</div>'
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
					'replacement' => '<hr class="c-bbcode-hr">{param}'
				],
				'---' => [
					'type' => 'replacement',
					'title' => '---',
					'replacement' => '<hr class="c-bbcode-hr">{param}'
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
				// underline
				'u' => [
					'type' => 'replacement',
					'title' => 'u',
					'replacement' => '<span class="c-bbcode-underline">{param}</span>'
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
		 * @var array these Helpers are also used in the Parser
		 */
		public $helpers = [
			'FileUpload.FileUpload',
			'MailObfuscator.MailObfuscator',
			'Geshi.Geshi',
			'Embedly.Embedly',
			'Html',
			'Text'
		];

		/**
		 * Initialized parsers
		 *
		 * @var array
		 */
		protected $_initializedParsers = array();

		public function beforeRender($viewFile) {
			if (isset($this->request) && $this->request->action === 'preview') {
				$this->Geshi->showPlainTextButton = false;
			}
		}

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
			if (empty($string) || $string === 'n/t') {
				return $string;
			}

			$defaults = ['return' => 'html', 'multimedia' => true];
			$options += $defaults;

			$id = md5(serialize($options) . $string);
			if (isset($this->_cache[$id])) {
				return $this->_cache[$id];
			}

			Stopwatch::start('Bbcode::parse');
			$this->_initParser($options);

			$string = $this->_Preprocessors->process($string);

			$this->_Parser->parse($string);

			$this->_Parser->accept(new JbbCodeNl2BrVisitor($this, $options));
			if ($this->_cSettings['autolink']) {
				$this->_Parser->accept(new JbbCodeAutolinkVisitor($this, $options));
			}
			if ($this->_cSettings['smilies']) {
				$this->_Parser->accept(new JbbCodeSmileyVisitor($this, $options));
			}

			switch ($options['return']) {
				case 'text':
					$html = $this->_Parser->getAsText();
					break;
				default:
					$html = $this->_Parser->getAsHtml();
			}

			$html = $this->_Postprocessors->process($html);

			$this->_cache[$id] = $html;
			Stopwatch::stop('Bbcode::parse');
			return $this->_cache[$id];
		}

		protected function _initSettings() {
			if ($this->_cSettings === null) {
				// @bogus: why not passed into helper as one array?
				$this->_cSettings = Configure::read('Saito.Settings') + $this->settings;
			}
		}

		protected function _initParser(&$options) {
			$this->_initSettings();

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
						$this->_Parser->addCodeDefinition(new $class($this, $options));
						break;
					default:
						throw new Exception();
				}
			}
		}

		/**
		 * Adds cite mark before text lines in textarea
		 *
		 * @param string $string
		 * @return string
		 */
		public function citeText($string) {
			if (empty($string)) {
				return '';
			}
			$this->_initSettings();
			$out = '';
			// split already quoted lines
			$citeLines = preg_split("/(^{$this->_cSetttings['quote_symbol']}.*?$\n)/m",
				$string,
				null,
				PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			foreach ($citeLines as $citeLine):
				if (mb_strpos($citeLine, $this->_cSettings['quote_symbol']) === 0) {
					// already quoted lines need no further processing
					$out .= $citeLine;
					continue;
				}
				// split [bbcode]
				$matches = preg_split('`(\[(.+?)=?.*?\].+?\[/\2\])`',
					$citeLine,
					null,
					PREG_SPLIT_DELIM_CAPTURE);
				$i = 0;
				$line = '';
				foreach ($matches as $match) {
					// the [bbcode] preg_split uses a backreference \2 which is in the $matches
					// but is not needed in the results
					// @todo elegant solution
					$i++;
					if ($i % 3 == 0) {
						continue;
					}
					// wrap long lines
					if (mb_strpos($match, '[') !== 0) {
						$line .= wordwrap($match);
					} else {
						$line .= $match;
					}
					// add newline to wrapped lines
					if (mb_strlen($line) > 60) {
						$out .= $line . "\n";
						$line = '';
					}
				}
				$out .= $line;
			endforeach;
			$out = preg_replace("/^/m", $this->_cSettings['quote_symbol'] . " ",
				$out);
			return $out;
		}

	}

