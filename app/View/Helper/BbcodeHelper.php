<?php

App::uses('AppHelper', 'View/Helper');
App::import('Vendor', 'stringparser_bbcode/stringparser_bbcode');

/**
 * @td Configure::read('Saito.Settings') should be argument or
 * make raw BBC class and a Subclass incorporating functions with 'Saito.Settings'
 */
interface MarkupParser {

	public function parse($string);

	public function citeText($string);

}

class BbcodeHelper extends AppHelper implements MarkupParser {

	public $helpers = array(
			'FileUpload.FileUpload',
			'MailObfuscator.MailObfuscator',
			'CakephpGeshi.Geshi',
      'Embedly.Embedly',
			'Html',
	);

	/**
	 * Array with domains from which embedding video is allowed
	 *
	 * array(
	 * 	'youtube' => 1,
	 *  'vimeo' => 1,
	 * );
	 *
	 * array('*' => 1) means every domain allowed
	 *
	 * @var array
	 */
	protected static $_allowedVideoDomains = null;

	/**
	 * Flash video domains embeddeble via https
	 */
	protected static $_flashVideoDomainsWithHttps = array(
			'vimeo' => 1,
			'youtube' => 1
	);

	/**
	 * These are the file exensions we are asume belong to audio files
	 *
	 * @var array
	 */
	protected static $html5_audio_extensions = array(
			'm4a',
			'ogg',
			'opus',
			'mp3',
			'wav',
	);
	protected static $_videoErrorMessage;

	public $settings = array(
		'quoteSymbol' => '»',
		'hashBaseUrl' => '', // Base URL for # tags.
		'atBaseUrl'   => '', // Base URL for @ tags.
		'atUserList'  => '' // User list for @ tags.
	);

	/**
	 * Markup Parser
	 */
	protected $_Parser;

	/**
	 * Initialized parsers
	 *
	 * @var array
	 */
	protected $_initializedParsers = array();

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		self::$_videoErrorMessage = new BbcodeMessage();
	}

	public function beforeRender($viewFile) {
		if ( isset($this->request) && $this->request->action === 'preview' ) {
			$this->Geshi->showPlainTextButton = false;
		}
	}

	/**
	 * Generates html from [bbcode]
	 *
	 * @param type $string
	 * @param array $options
	 * @return string
	 */
	public function parse($string, array $options = array( )) {
		$this->_initParser($options);
		$string = $this->_Parser->parse($string);
		return $string;
	}

	/**
	 * Setup StringParser_BBCode();
	 *
	 * @param array $options
	 */
	protected function _initParser(array $options = array( )) {

		$defaults = array(
				// allows to supress the output of media elements
				'multimedia' => true,
		);
		$options = array_merge($defaults, $options);

		$fp = md5(serialize($options));
		if (isset($this->_initializedParsers[$fp])) {
			$this->_Parser = $this->_initializedParsers[$fp];
			return;
		}

		extract($options);

		$this->_Parser = ClassRegistry::init('StringParser_BBCode');

		//* newline
		$this->_Parser->addFilter(STRINGPARSER_FILTER_PRE,
				array( &$this, '_convertLineBreaks' ));
		$this->_Parser->addParser(array( 'block', 'inline', 'listitem' ), 'nl2br');

		/*
		 * Support for deprecated [img#] tag. [upload] is used now.
     *
     * Remove if appropriate 2011-06-20.
		 */
    $this->_Parser->addFilter(STRINGPARSER_FILTER_PRE,
        // Translates [img# foo]bar[/img] to [upload foo]bar[/upload]
        function($string) {
          return preg_replace(
              '#\[img\#(.*)?\](.+?)\[/img\]#is',
              "[upload\\1]\\2[/upload]",
              $string
              );
        }
        );

		if (empty($this->settings['hashBaseUrl']) === false) {
			// #<numeric> internal links
			$this->_Parser->addFilter(
				STRINGPARSER_FILTER_PRE,
				array(&$this, '_hashLinkInternal')
			);
		}


		if (empty($this->settings['atBaseUrl']) === false) {
			// @<char> internal links
			$this->_Parser->addFilter(
				STRINGPARSER_FILTER_PRE,
				array(&$this, '_atLinkInternal')
			);
		}

		//* [code]
		$this->_Parser->addCode(
				'code', 'usecontent', array( &$this, "_code" ), array( ), 'code',
				array( 'block' ), array( )
		);

		//* bold
		$this->_Parser->addCode(
				'b', 'simple_replace', null,
				array( 'start_tag' => '<strong>', 'end_tag' => '</strong>' ), 'inline',
				array( 'block', 'inline', 'link', 'listitem' ), array( )
		);

		//* italic
		$this->_Parser->addCode(
				'i', 'simple_replace', null,
				array( 'start_tag' => '<em>', 'end_tag' => '</em>' ), 'inline',
				array( 'block', 'inline', 'link', 'listitem' ), array( )
		);

		//* underline
		$this->_Parser->addCode(
				'u', 'simple_replace', null,
				array( 'start_tag' => "<span class='c_bbc_underline'>", 'end_tag' => '</span>' ),
				'inline', array( 'block', 'inline', 'link', 'listitem' ), array( )
		);

		//* strike
		$this->_Parser->addCode(
				'strike', 'simple_replace', null,
				array( 'start_tag' => "<del>", 'end_tag' => '</del>' ), 'inline',
				array( 'block', 'inline', 'link', 'listitem' ), array( )
		);

		//* hr
		$this->_Parser->addCode(
				'---', 'simple_replace', null,
				array( 'start_tag' => '<hr class="c_bbc_hr">', 'end_tag' => '</hr>' ), 'inline',
				array( 'block' ), array( )
		);
		$this->_Parser->addCode(
				'hr', 'simple_replace', null,
				array( 'start_tag' => '<hr class="c_bbc_hr">', 'end_tag' => '</hr>' ), 'inline',
				array( 'block' ), array( )
		);

		//* urls
		$this->_Parser->addCode(
				'url', 'usecontent?', array( &$this, '_url' ),
				array( 'usecontent_param' => 'default' ), 'link',
				array( 'block', 'inline', 'listitem' ), array( )
		);

		//* link
		$this->_Parser->addCode(
				'link', 'usecontent?', array( &$this, '_url' ),
				array( 'usecontent_param' => 'default' ), 'link',
				array( 'block', 'inline', 'listitem' ), array( )
		);

		//* email
		$this->_Parser->addCode(
				'email', 'usecontent?', array( &$this, '_email' ),
				array( 'usecontent_param' => 'default' ), 'link',
				array( 'block', 'inline' ), array( )
		);

		//* lists
		$this->_Parser->addCode(
				'list', 'simple_replace', null,
				array( 'start_tag' => '<ul class="c_bbc_ul">', 'end_tag' => '</ul>' ),
				'list', array( 'block', 'listitem', 'quote' ), array( ));
		$this->_Parser->setCodeFlag('list', 'paragraph_type',
				BBCODE_PARAGRAPH_BLOCK_ELEMENT);
		$this->_Parser->setCodeFlag('list', 'closetag.after.newline',
				BBCODE_NEWLINE_IGNORE);
		$this->_Parser->setCodeFlag('list', 'opentag.before.newline',
				BBCODE_NEWLINE_DROP);
		$this->_Parser->setCodeFlag('list', 'closetag.before.newline',
				BBCODE_NEWLINE_DROP);

		//* listitem
		$this->_Parser->addCode(
				'*', 'simple_replace', null,
				array( 'start_tag' => '<li class="c_bbc_li">', 'end_tag' => '</li>' ),
				'listitem', array( 'list' ), array( )
		);
		$this->_Parser->setCodeFlag('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);

		//* smilies
		if ( Configure::read('Saito.Settings.smilies') ):
			$this->_Parser->addFilter(STRINGPARSER_FILTER_PRE, array( &$this, '_smilies' ));
		endif;

		//* quote
		$this->_Parser->addParser(array( 'block', 'inline' ),
				array( &$this, '_quote' ));

		//* autolinks
		if ( Configure::read('Saito.Settings.autolink') ) {
			$this->_Parser->addFilter(STRINGPARSER_FILTER_PRE,
					'BbcodeHelper::_autoLinkPre');
		}

		// open external links in new browser
		$this->_Parser->addFilter(STRINGPARSER_FILTER_POST, 'BbcodeHelper::_relLink');

		//* allows [url=<foo> label=none] to be parsed as [url default=<foo> label=none]
		$this->_Parser->setMixedAttributeTypes(true);

		if ( Configure::read('Saito.Settings.bbcode_img') && $multimedia ):

			// video - iframe
			$this->_Parser->addCode(
					'iframe', 'usecontent', 'BbcodeHelper::_iframe',
					array( 'usecontent_param' => 'default' ), 'img',
					array( 'block', 'inline' ), array( )
			);

			// video - flash
			$this->_Parser->addCode(
					'flash_video', 'usecontent', 'BbcodeHelper::_flashVideo',
					array( 'usecontent_param' => 'default' ), 'img',
					array( 'block', 'inline' ), array( )
			);

			// video - html5
			$this->_Parser->addCode(
					'video', 'usecontent', 'BbcodeHelper::_html5Video',
					array( 'usecontent_param' => 'default' ), 'img',
					array( 'block', 'inline' ), array( )
			);

			// audio - html5
			$this->_Parser->addCode(
					'audio', 'usecontent', 'BbcodeHelper::_html5Audio',
					array( 'usecontent_param' => 'default' ), 'img',
					array( 'block', 'inline' ), array( )
			);

			// external images
			$this->_Parser->addCode(
					'img', 'usecontent', array( &$this, '_externalImage' ),
					array( 'usecontent_param' => 'default' ), 'img',
					array( 'block', 'inline', 'link' ), array( )
			);

			// image upload
			$this->_Parser->addCode(
					'upload', 'usecontent', array( &$this, '_upload' ),
					array( 'usecontent_param' => 'default' ), 'img',
					array( 'block', 'inline', 'link' ), array( )
			);

			$this->_Parser->addCode(
				'embed', 'usecontent', array( &$this, '_embed' ),
				array( 'usecontent_param' => 'default' ), 'embed',
				array( 'block'), array( )
			);

		endif;

		$this->_initializedParsers[$fp] = $this->_Parser;
	}

	/**
	 * Consolidates '\n\r', '\r' to `\n`
	 *
	 * @param string $string
	 * @return string
	 */
	public function _convertLineBreaks($string) {
		return preg_replace('/\015\012|\015|\012/', "\n", $string);
	}

	/**
	 * @mlf Just rebuild the smily system
	 * @param <type> $string
	 * @return <type>
	 */
	public function _smilies($string, $options = array( )) {
		$defaults = array(
				'cache' => true,
		);

		$options = array_merge($defaults, $options);
		extract($options);

//		Stopwatch::start('_smilies');

		// Building Smilies
		// @td refactor: MVC|method?
		$smilies = Configure::read('Saito.Smilies.smilies_all');

		$build_cache = false;

		if ( !$s = Configure::read("Saito.Smilies.smilies_all_html") ) {
			if ( !$cache || !$s = Cache::read("Saito.Smilies.smilies_all_html") ) {
				$build_cache = true;
			}
		}

		if ( $build_cache ):
			$s['codes'] = array( );
			$s['replacements'] = array( );
			$s = array( );
			foreach ( $smilies as $smiley ):
				$s['codes'][] = $smiley['code'];
				$s['replacements'][] = $this->Html->image('smilies/' . $smiley['image'],
						array( 'alt' => "{$smiley['code']}", 'title' => $smiley['title'] ));
			endforeach;
			Configure::write("Saito.Smilies.smilies_all_html", $s);
			if ( $cache ) {
				Cache::write("Saito.Smilies.smilies_all_html", $s);
			}
		endif;

		$additionalButtons = Configure::read('Saito.markItUp.additionalButtons');
		if (!empty($additionalButtons)):
			foreach ( $additionalButtons as $additionalButtonTitle => $additionalButton):
				// $s['codes'][] = ':gacker:';
				$s['codes'][] = $additionalButton['code'];
				// $s['replacements'][] = $this->Html->image('smilies/gacker_large.png');
				if ( $additionalButton['type'] === 'image' ):
					$additionalButton['replacement'] = $this->Html->image('markitup'.DS.$additionalButton['replacement']);
				endif;
				$s['replacements'][] = $additionalButton['replacement'];
			endforeach;
		endif;

		// prevents parsing of certain areas
		$string_array = preg_split("/
			(
				(?:						# bbcode commands esp. url being replace with smilies
					\[[^\[]*?\] 	# opening brackets of bbcode, e.g. [url=foo]
					[^\[]*? 			# middle part between brackets, e.g. linkname
					\[\/.*?\]			# end brackets of bbcode, e.g. [\/url]
				)
			|								# or
				(?:&[^\s]*;)			# html entities
			)
			/xis",
				$string, 0, PREG_SPLIT_DELIM_CAPTURE);

		foreach ( $string_array as $key => $value ) {
			if ( $key % 2 == false ) {
				$string_array[$key] = str_replace($s['codes'], $s['replacements'], $value);
			}
		}

//		Stopwatch::stop('_smilies');
		return implode('', $string_array);
	}

	/**
	 * automaticaly generate links from raw http:// source without [URL]
	 *
	 * @param string $string
	 * @return string
	 */
	public static function _autoLinkPre($string) {
		//* autolink http://urls
		$string = preg_replace(
				"#(?<=^|[\n ])(?P<content>[\w]+?://.*?[^ \"\n\r\t<]*)#is", "[url]\\1[/url]",
				$string
		);

		//* autolink without http://, i.e. www.foo.bar/baz
		$string = preg_replace(
				"#(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:/[^ \"\t\n\r<]*)?)#is",
				"\\1[url=http://\\2]\\2[/url]", $string
		);

		//* autolink email
		$string = preg_replace("
				#(?<=^|[\n ])(?P<content>([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+))#i",
				"[email]\\1[/email]", $string
		);

		return $string;
	}

	public function _code($action, $attributes, $content, $params, &$node_object) {
		$type = 'text';
		if ( !empty($attributes) ):
			$type = key($attributes);
		endif;

		$this->Geshi->defaultLanguage = 'text';
		// allow all languages
		$this->Geshi->validLanguages = array(true);

		$string = '<div class="c_bbc_code-wrapper"><pre lang="' . $type . '">' . $content . '</pre></div>';
		$string = $this->Geshi->highlight($string);
		return $string;
	}

	public static function _iframe($action, $attributes, $content, $params, &$node_object) {
		$out = '<iframe';

		foreach ( $attributes as $attributeName => $attributeValue ):

			//* check if host is allowed
			if ( $attributeName === 'src' ):
				if ( self::_isVideoDomainAllowed($attributeValue) === false ) :
					return self::$_videoErrorMessage->get();
				endif;
				if ( strpos($attributeValue, '?') === false ) :
					$attributeValue .= '?';
				endif;
				$attributeValue .= '&wmode=Opaque';
			endif;

			$out .= " $attributeName=\"$attributeValue\" ";
		endforeach;

		$out .= '></iframe>';
		return $out;
	}

	/**
	 * Inserts a html5 video tag
	 *
	 * @param <type> $url
	 * @param <type> $width
	 * @param <type> $height
	 */
	public static function _html5Video($action, $attributes, $content, $params, &$node_object) {
		// fix audio files mistakenly wrapped into an [video] tag
		if ( preg_match('/(' . implode('|', self::$html5_audio_extensions) . ')$/i',
						$content) === 1 ) {
			return self::_html5Audio(null, null, $content);
		}

		$out = "<video src='$content' controls='controls' x-webkit-airplay='allow'>";
		$out .= BbcodeMessage::format(__(
								'Your browser does not support HTML5 video. Please updgrade to a modern' .
								'browser. In order to watch this stream you need an HTML5 capable browser.',
								true));
		$out .= '</video>';
		return $out;
	}

	/**
	 * Wraps and url into html5 audio element
	 *
	 * @param string $url
	 * @return string
	 * @access protected
	 */
	public static function _html5Audio($action, $attributes, $content, $params = null, &$node_object = null) {
		// @lo
		$out = "<audio src='$content' controls='controls'>";
		$out .= BbcodeMessage::format(__(
								'Your browser does not support HTML5 audio. Please updgrade to a modern' .
								'browser. In order to watch this stream you need an HTML5 capable browser.',
								true));
		$out .= "</audio>";
		return $out;
	}

	/**
	 * @td search for backery class
	 */
	public static function _flashVideo($action, $attributes, $content, $params = null, &$node_object = null) {
		preg_match("#(?P<url>.+?)\|(?P<width>.+?)\|(?<height>\d+)#is", $content,
				$matches);
		if ( !isset($matches['height']) ) {
			return '<em>Flash nicht erkannt</em>';
		}
		extract($matches);

		if ( self::_isVideoDomainAllowed($url) === false ) :
			return self::$_videoErrorMessage->get();
		endif;

		if (env('HTTPS')) {
			if (isset(self::$_flashVideoDomainsWithHttps[self::_getDomainForUri($url)])) {
				$url = str_ireplace('http://', 'https://', $url);
			}
		}

		$out = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="' . $width . '" height="' . $height . '">
									<param name="movie" value="' . $url . '"></param>
									<embed src="' . $url . '" width="' . $width . '" height="' . $height . '" type="application/x-shockwave-flash" wmode="opaque" style="width:' . $width . 'px; height:' . $height . 'px;" id="VideoPlayback" type="application/x-shockwave-flash" flashvars=""> </embed>
							</object>';
		return $out;
	}

	public function _upload($action, $attributes, $content, $params, &$node_object) {
		if ( empty($attributes) ) {
			$this->FileUpload->reset();
			return "<div class='c_bbc_upload'>" . $this->FileUpload->image($content) . "</div>";
		} else {
			$this->FileUpload->reset();
      $allowedKeys = array_fill_keys(array( 'width', 'height'), false);
      $allowedAttributes = array_intersect_key($attributes, $allowedKeys);
			return "<div class='c_bbc_upload'>" . $this->FileUpload->image($content,
							array(
                  'autoResize' => false,
                  'resizeThumbOnly' => false,
                  ) + $allowedAttributes
          ) . "</div>";
		}
	}

	/**
	 * Provides links for external images
	 *
	 * @param string $url
	 * @param string $params
	 * @return type
	 */
	public function _externalImage($action, $attributes, $content, $params, &$node_object) {
		$options = array(
				'class' => 'c_bbc_external-image',
				'width' => 'auto',
				'height' => 'auto',
		);

		$url = $content;

		// process [img=(parameters)] parameters
		if ( !empty($attributes['default']) ) {
			$default = trim($attributes['default']);
			switch ( $default ) :
				case 'left':
					$options['style'] = 'float: left;';
					break;
				case 'right':
					$options['style'] = 'float: right;';
					break;
				default :
					preg_match('/(\d{0,3})(?:x(\d{0,3}))?/i', $default, $dimension);
					// $dimenson for [img=50] or [img=50x100]
					// [0] (50) or (50x100)
					// [1] (50)
					// [2] (100)
					if ( !empty($dimension[1]) ) {
						$options['width'] = $dimension[1];
						if ( !empty($dimension[2]) ) {
							$options['height'] = $dimension[2];
						}
					}
			endswitch;
		}

		return $this->Html->image($url, $options);
	}

	/**
	 * Adds cite mark before text lines in textarea
	 *
	 * @param string $string
	 * @return string
	 */
	public function citeText($string) {
		$out = '';
		if ( !empty($string) ):
			// split already quoted lines
			$citeLines = preg_split("/(^{$this->settings['quoteSymbol']}.*?$\n)/m", $string, null,
					PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			foreach ( $citeLines as $citeLine ):
				if ( mb_strpos($citeLine, $this->settings['quoteSymbol']) === 0 ):
					// already quoted lines need no further processing
					$out .= $citeLine;
					continue;
				endif;
				// split [bbcode]
				$matches = preg_split('`(\[(.+?)=?.*?\].+?\[/\2\])`', $citeLine, null,
						PREG_SPLIT_DELIM_CAPTURE);
				$i = 0;
				$line = '';
				foreach ( $matches as $match ):
					/*
					 * the [bbcode] preg_split uses a backreference \2 which is in the $matches
					 * but is not needed in the results
					 * @td elegant solution
					 */
					$i++;
					if ( $i % 3 == 0 ):
						continue;
					endif;

					if ( mb_strpos($match, '[') !== 0 ):
						$line .= wordwrap($match);
					else:
						$line .= $match;
					endif;
					if ( mb_strlen($line) > 60 ):
						$out .= $line . "\n";
						$line = '';
					endif;
				endforeach;
				$out .= $line;
			endforeach;
			$out = preg_replace("/^/m", $this->settings['quoteSymbol'] . " ", $out);
		endif;
		return $out;
	}

	public function _email($action, $attributes, $content, $params, &$node_object) {
		if ( isset($attributes['default']) ):
			$url = $attributes['default'];
			$text = $content;
		else:
			$url = $content;
			$text = $content;
		endif;

		$url = str_replace('mailto:', '', $url);
		return "<a href='mailto:$url'>$text</a>";
		// return $this->MailObfuscator->createLink($url, $text);
	}

	public function _hashLinkInternal($string) {
		$string = preg_replace(
			'/(\s|^)#(\d+)/',
			"\\1[url={$this->settings['hashBaseUrl']}\\2 label=none]#\\2[/url]",
			$string
		);
		return $string;
	}

	public function _atLinkInternal($string) {
		$tags = array();
		if (preg_match_all('/(\s|^)@([^\s\pP]+)/m', $string, $tags)) {
			$users = $this->settings['atUserList'];
			sort($users);
			$names = array();
			if (empty($tags[2]) === false) {
				$tags = $tags[2];
				foreach ($tags as $tag) {
					if (in_array($tag, $users)) {
						$names[$tag] = 1;
					} else {
						$continue = 0;
						foreach ($users as $user) {
							if (mb_strpos($user, $tag) === 0){
								$names[$user] = 1;
								$continue = true;
							}
							if ($continue === false) {
								break;
							} elseif ($continue !== 0) {
								$continue = false;
							}
						}
					}
				}
			}
			krsort($names);
			foreach($names as $name => $v) {
				$urlName = urlencode($name);
				$string = preg_replace(
					"/@$name/",
					"[url={$this->settings['atBaseUrl']}{$urlName} label=none]@{$name}[/url]",
					$string
				);
			}
		}
		return $string;
	}

	/**
	 * url urls and [URL]
	 */
	public function _url($action, $attributes, $content, $params, &$node_object) {

		$defaults = array(
				'label' => true,
		);

		$wasShort = false;
		if ( isset($attributes['default']) ):
			// [url=...]...[/url]
			$url = $attributes['default'];
			$text = $content;
		else:
			// [url]...[/url]
			$url = $content;
			$text = $content;
			$wasShort = true;
		endif;

		$options = array_merge($defaults, $attributes);

		if ($wasShort) {
			$text = $this->_truncate($text);
		}

		$out = "<a href='$url'>$text</a>";

		//* add domain info: `[url=domain.info]my link[/url]` -> `my link [domain.info]`
		$label = $options['label'];
		if ($label !== 'none' && $label !== 'false' && $label !== false && $wasShort === false) {
			if (!empty($url) && preg_match('/\<img\s*?src=/', $text) !== 1) {
				$host = self::_getDomainAndTldForUri($url);
				if ($host !== false && $host !== env('SERVER_NAME')) {
					$out .= ' <span class=\'c_bbc_link-dinfo\'>[' . $host . ']</span>';
				}
			}
		}
		return $out;
	}

	/**
	 * Marks an <a> link as external to open in a new browser window
	 *
	 * @param array $matches
	 * @return string
	 */
	public static function _relLink($string) {
		return preg_replace_callback('#href=["\'](.*?)["\']#is',
						'BbcodeHelper::_relLinkCallback', $string);
	}

	protected static function _relLinkCallback($matches) {
		$out = '';
		$url = $matches[1];

		// preventing error message for parse_url('http://');
		if ( substr($url, -3) === '://' )
			return $matches[0];
		$parsed_url = @parse_url($url);

		if ( isset($parsed_url['host']) ) {
			if ( $parsed_url['host'] !== env('SERVER_NAME') && $parsed_url['host'] !== "www." . env('SERVER_NAME') ) {
				$out = " rel='external' target='_blank'";
			}
		}
		return $matches[0] . $out;
	}

  public function _embed($action, $attributes, $content, $params, &$node_object) {
    if ( Configure::read('Saito.Settings.embedly_enabled') == false ):
      return __('[embed] tag not enabled.');
    endif;

    if ( $action === 'validate' ) :
      return true;
    endif;

    $out = 'Embeding failed.';
    $this->Embedly->setApiKey(Configure::read('Saito.Settings.embedly_key'));
    $embedly = $this->Embedly->embedly($content);
    if ( $embedly !== false ) :
      $out = $embedly;
    endif;

    return $out;
  }

	/**
	 * Formats quote.
	 *
	 * @param string $string
	 * @return string
	 */
	public function _quote($string) {
		$quote_symbol_sanitized = Sanitize::html($this->settings['quoteSymbol']);
		$string = preg_replace(
				// Begin of the text or a new line in the text, maybe one space afterwards
				'/(^|\n\r\s?)'
				. $quote_symbol_sanitized
				. '\s([^\n\r]*)/m',
				"\\1<span class=\"c_bbc_citation\">" . $quote_symbol_sanitized . " \\2</span>",
				$string
		);
		return $string;
	}

	/**
	 * @bogus does this truncate strings or the longest word in the string or what?
	 * @bogus what about [url=][img]...[/img][url]. Is the [img] url trunctated too?
	 *
	 * @param type $string
	 * @return string
	 */
	protected function _truncate($string) {
		$text_word_maxlength = Configure::read('Saito.Settings.text_word_maxlength');
		$substitue_char = ' … ';

		if ( mb_strlen($string) > $text_word_maxlength ) {
			$left_margin = (int) floor($text_word_maxlength / 2);
			$right_margin = (int) (-1 * ($text_word_maxlength - $left_margin - mb_strlen($substitue_char)));

			$string = mb_substr($string, 0, $left_margin) . $substitue_char . mb_substr($string,
							$right_margin);
		}
		return $string;
	}

	/**
	 * Checks if an domain is allowed by comparing it to the admin preference
   *
	 * @param string $src
	 * @return bool
	 */
	protected static function _isVideoDomainAllowed($src) {
		self::$_videoErrorMessage->reset();

		// initialy setup self::$_allowedVideoDomains
		if ( self::$_allowedVideoDomains === null ):
      // @td bad mvc; set allowed domains when initizlizing helper in controller
			self::$_allowedVideoDomains = trim(Configure::read('Saito.Settings.video_domains_allowed'));
      // @td bad mvc; the array should be created by the Settings Model
			self::$_allowedVideoDomains = array_fill_keys(
					array_map(
							create_function('$in', 'return trim($in);'),
							explode('|', self::$_allowedVideoDomains)
					), 1);
		endif;

		// `*` admin pref allows all domains
		if ( self::$_allowedVideoDomains === array( '*' => 1 ) ):
			return true;
		endif;

		// check if particular domain is allowed
		$host = self::_getDomainForUri($src);
		if ( isset(self::$_allowedVideoDomains[$host]) === true ):
			return true;
		else:
			self::$_videoErrorMessage->set(
					sprintf(
							__('Domain <strong>%s</strong> not allowed for embedding video.'),
							$host
					)
			);
		endif;

    // In case we didn't catch it above, just say No.
		if ( empty(self::$_videoErrorMessage) ):
			self::$_videoErrorMessage->set(__('Video domain is not allowed.'));
		endif;

		return false;
	}

	/**
	 * Returns host name for $uri
	 *
	 * `http://www.youtube.com/foo` returns `youtube`
	 *
	 * @param string $uri
	 * @return string
	 */
	protected static function _getDomainForUri($uri) {
		return self::_getDomainAndTldForUri($uri, 'domain');
	}

	/**
	 * Returns top level domain
	 */
	protected static function _getDomainAndTldForUri($uri, $part = 'fulldomain' ) {
		$host = @parse_url($uri, PHP_URL_HOST);
		if (!empty($host) && $host !== false) :
			if ( preg_match('/(?P<fulldomain>(?P<domain>[a-z0-9][a-z0-9\-]{1,63})\.(?<tld>[a-z\.]{2,6}))$/i',
							$host, $regs) ) {
				if(!empty($regs[$part])) {
					return $regs[$part];
				}
			}
		endif;
		return false;
	}
}

class BbcodeMessage {

	protected $_message = '';

	public function reset() {
		$this->_message = '';
	}

	public function set($message) {
		$this->_message = $message;
	}

	public function get() {
		return self::format($this->_message);
	}

	public static function format($message) {
		return "<div class='c_bbc_imessage'>$message</div>";
	}

}
