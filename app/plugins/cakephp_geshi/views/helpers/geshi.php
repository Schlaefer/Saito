<?php
/**
 * Geshi Helper
 *
 * Implements geshi syntax highlighting for cakephp
 * Originally based off of http://www.gignus.com/code/code.phps
 *
 * @author Mark story
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * Copyright 2008-2010 Mark Story 
 * 
 * 694B The Queensway
 * toronto, ontario
 * M8Y 1K9, Canada
 * 
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */
App::import('Vendor', 'geshi/geshi');

class GeshiHelper extends AppHelper {
/**
 * Path the configuration file can be found on.
 *
 * @var string
 */
	public $configPath;

/**
 * The Container Elements that could contain highlightable code
 *
 * @var array
 **/
	public $validContainers = array('pre');

/**
 * Replace containers with divs to increase validation
 *
 * @var string
 */
	public $containerMap = array('pre' => array('div class="code"', 'div'));

/**
 * The languages you want to highlight.
 *
 * @var array
 **/
	public $validLanguages = array('css', 'html', 'php', 'javascript', 'python', 'sql');

/**
 * Default language to use if no valid language is found.  leave null to require a language attribute
 * to be set on each container.
 *
 * @var mixed  false for no default language, String for the default language
 **/
	public $defaultLanguage = false;

/**
 * The Attribute use for finding the code Language. 
 * 
 * Common choices are lang and class
 *
 * @var string
 **/
	public $langAttribute = 'lang';

/**
 * GeSHi Instance
 *
 * @var object
 **/
	protected $_geshi = null;

/**
 * Show the Button that can be used with JS to switch to plain text.
 *
 * @var bool
 */	
	public $showPlainTextButton = true;


/**
 * Highlight a block of HTML containing defined blocks.  Converts blocks from plain text
 * into highlighted code.
 * 
 *
 * @param string $htmlString 
 * @return void
 * @author Mark Story
 */
	function highlight( $htmlString ) {
		$tags = implode('|', $this->validContainers);
		//yummy regex
		$pattern = '#(<('. $tags .')[^>]'.$this->langAttribute.'=["\']+([^\'".]*)["\']+>)(.*?)(</\2\s*>|$)#s';
		/*
			matches[0] = whole string
			matches[1] = open tag including lang attribute
			matches[2] = tag name
			matches[3] = value of lang attribute
			matches[4] = text to be highlighted
			matches[5] = end tag
		*/	
		$html = preg_replace_callback($pattern, array($this, '_processCodeBlock'), $htmlString);
		return $this->output( $html );
	}
		
/**
 * Preg Replace Callback
 * Uses matches made earlier runs geshi returns processed code blocks.
 *
 * @return string Completed replacement string
 **/
	protected function _processCodeBlock($matches) {
		list($block, $openTag, $tagName, $lang, $code, $closeTag) = $matches;
		unset($matches);
		//check language
		$lang = $this->validLang($lang);
		$code = utf8_encode(html_entity_decode($code, ENT_QUOTES)); //decode text in code block as GeSHi will re-encode it.
		
		if (isset($this->containerMap[$tagName])) {
			$patt = '/' . preg_quote($tagName) . '/';
			$openTag = preg_replace($patt, $this->containerMap[$tagName][0], $openTag);
			$closeTag = preg_replace($patt, $this->containerMap[$tagName][1], $closeTag);
		}
		
		if ($this->showPlainTextButton) {
			$button = '<a href="#" class="geshi-plain-text">Show Plain Text</a>';
			$openTag = $button . $openTag;
		}
		
		if ((bool)$lang) {
			//get instance or use stored instance
			if ($this->_geshi == null) {
				$geshi = new GeSHI(trim($code), $lang);
				$this->__configureInstance($geshi);
				$this->_geshi = $geshi;
			} else {
				$this->_geshi->set_source(trim($code));
				$this->_geshi->set_language($lang);
			}
			$highlighted = $this->_geshi->parse_code();
			return $openTag . $highlighted . $closeTag;
		}
		return $openTag . $code . $closeTag;
	}
/**
 * Check if the current language is a valid language.
 *
 * @return mixed.
 **/
	protected function validLang( $lang )  {
		if (in_array($lang, $this->validLanguages)) { 
			return $lang; 
		}
		if ($this->defaultLanguage) {
			return $this->defaultLanguage;
		}
		return false;
	}
	
/**
 * Configure a geshi Instance the way we want it. 
 * app/config/geshi.php
 *
 * @return void
 **/
	private function __configureInstance($geshi) {
		$this->configPath = APP . 'Config' . DS;

		if (file_exists($this->configPath . 'geshi.php')) {
			include $this->configPath . 'geshi.php';
		}
	}
} 