<?php
/**
 * Flattr Donate Button
 * @link http://flattr.com/support/integrate/js
 * 2010-12-11 ms
 */
class FlattrHelper extends AppHelper {
 
	public $helpers = array('Html');
 
	const API_URL = 'http://api.flattr.com/';
 
 
	/**
	 * display the FlattrButton
	 * @param mixed $url (unique! neccessary)
	 * @param array $options
	 * 2010-12-19 ms
	 */
	function button($url, $options = array(), $attr = array()) {
		if (empty($options['uid'])) {
			$options['uid'] = Configure::read('Flattr.uid');
		}
		$categories = array();
 
		$defaults = array(
			'mode' => 'auto',
			'description'	=> $_SERVER['HTTP_HOST'],
			'language' => 'en_US',
			'category' => 'text',
			'button' => 'default', # none or compact
			'tags' => array(),
			//'hidden' => '',
			//'description' => '',
		);
		$options = array_merge($defaults, $options);
 
		$mode = $options['mode'];
		unset($options['mode']);
		if (is_array($options['tags'])) {
			$options['tags'] = implode(',', $options['tags']);
		}

		$description = $options['description'];
		unset($options['description']);
 
		$rev = array();
		foreach ($options as $key => $option) {
			$rev[] = $key.':'.$option;
		}
		$linkOptions = array(
			'title' => $_SERVER['HTTP_HOST'],
			'class' => 'FlattrButton',
			'style' => 'display:none;',
			'rel' => 'flattr;'.implode(';', $rev)
		);
		$linkOptions = array_merge($linkOptions, $attr);
 
		$js = "(function() {
    var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
    s.type = 'text/javascript';
    s.async = true;
    s.src = '".self::API_URL."js/0.6/load.js?mode=".$mode."';
    t.parentNode.insertBefore(s, t);
})();";
		$code = $this->Html->link($description, $this->Html->url($url, true), $linkOptions);
		$code .= $this->Html->scriptBlock($js, array('inline' => true));
		return $code;
	}
}
?>