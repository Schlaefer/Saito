<?php
	App::uses('AppHelper', 'View/Helper');

/** angepasst für macnemo: inline options */


/**
 * Inline for Ajax Links
 *
 * Helper for AJAX operations.
 *
 * Helps doing AJAX using the JQuery library.
 *
 * PHP versions 4 and 5
 *
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2009, Loadsys Consulting, Inc. (http://www.loadsys.com)
 * @version       $1.0$
 * @modifiedby    $LastChangedBy: Donatas Kairys (Loadsys) $
 * @lastmodified  $Date: 2009-05-01$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class AjaxHelper extends AppHelper {
/**
 * Included helpers.
 *
 * @var array
 */
	var $helpers = array('Html', 'Form');
/**
 * HtmlHelper instance
 *
 * @var object
 * @access public
 */
//	var $Html = null;
/**
 * JavaScriptHelper instance
 *
 * @var object
 * @access public
 */
//	var $Javascript = null;
/**
 * Names of Javascript callback functions.
 *
 * @var array
 */
//	var $callbacks = array('complete', 'create', 'exception', 'failure', 'interactive', 'loading', 'loaded', 'success', 'uninitialized');
	var $callbacks = array('beforeSend', 'complete', 'error', 'success', 'beforeSubmit');
/**
 * Names of AJAX options.
 *
 * @var array
 */
	var $ajaxOptions = array('async', 'beforeSend', 'cache', 'complete', 'contentType', 'data', 'dataType', 'error', 'global', 'isModified', 'jsonp', 'password', 'processData', 'success', 'timeout', 'type', 'update', 'url', 'username', 'target', 'beforeSubmit');

/**
 * Names of additional Ajax Form Options
 *
 * @var array
 */
	var $ajaxFormOptions = array('target', 'beforeSubmit', 'semantic', 'resetFrom', 'clearForm');

	var $editorOptions = array('id', 'name', 'loadurl', 'type', 'data', 'style', 'callback', 'submitdata', 'method', 'rows', 'cols', 'width', 'loadtype', 'loaddata', 'onblur', 'cancel', 'submit', 'tooltip', 'placeholder', 'ajaxOptions');

	var $ajaxFormCallbacks = array('beforeSubmit');
/**
 * Output buffer for Ajax update content
 *
 * @var array
 */
	var $__ajaxBuffer = array();
	
	function beforeRender() {
	   if ( ClassRegistry::getObject('view') ) {
//		$this->Javascript->link('jquery', false);
//		$this->Javascript->link('jquery.form', false);
	   }
	}

	function link($title, $href = null, $options = array(), $confirm = null, $escapeTitle = true) {
		if (!isset($href)) {
			$href = $title;
		}

		if (!isset($options['url'])) {
			$options['url'] = $href;
		}

		if (isset($confirm)) {
			$options['confirm'] = $confirm;
			unset($confirm);
		}

		$htmlOptions = $this->__getHtmlOptions($options);
		if ( isset($htmlOptions['confirm']) ) {
			unset($htmlOptions['confirm']);
		}

		/*if (empty($options['fallback']) || !isset($options['fallback'])) {
			$options['fallback'] = $href;
		}*/

		if (!isset($htmlOptions['id'])) {
			$htmlOptions['id'] = 'link' . intval(rand());
		}

		if (!isset($htmlOptions['onclick'])) {
			$htmlOptions['onclick'] = '';
		}

		$inline = false;
		if (isset($options['inline'])) {
			$inline = $options['inline'];
		}

		$htmlOptions['onclick'] .= ' return false;';
		$return = $this->Html->link($title, '#', $htmlOptions, null, $escapeTitle);
		$script = $this->Html->scriptBlock(
			"jQuery('#{$htmlOptions['id']}').click( function() {" . $this->remoteFunction($options) . "; return false;});"
			, array( 'inline' => $inline )
		);

		if (is_string($script)) {
			$return .= $script;
		}

		return $return;
	}
/**
 * Creates JavaScript function for remote AJAX call
 *
 * This function creates the javascript needed to make a remote call
 * it is primarily used as a helper for AjaxHelper::link.
 *
 * @see AjaxHelper::link() for docs on options parameter.
 * @param array $options options for javascript
 * @return string html code for link to remote action
 */
	function remoteFunction($options = null, $function = 'jQuery.ajax') {
		if ( isset($options['confirm']) ) {
			$confirm = $options['confirm'];
			unset($options['confirm']);
		}
		$func = "{$function}(" . $this->__optionsForAjax($options) . ")";

		if (isset($options['before'])) {
			$func = "{$options['before']}; $func";
		}

		if (isset($options['after'])) {
			$func = "$func; {$options['after']};";
		}

		if (isset($options['condition'])) {
			$func = "if ({$options['condition']}) { $func; }";
		}

		if (isset($confirm)) {
			$func = "if (confirm('" . $this->Javascript->escapeString($confirm)
				. "')) { $func; } else { event.returnValue = false; return false; }";
		}
		return $func;
	}
/**
 * Returns a button input tag that will submit using Ajax
 *
 * Returns a button input tag that will submit form using XMLHttpRequest in the background instead of regular
 * reloading POST arrangement. <i>options</i> argument is the same as in <i>form_remote_tag</i>
 *
 * @param string $title Input button title
 * @param array $options Callback options
 * @return string Ajaxed input button
 */
	function submit($title = 'Submit', $options = array()) {
		$htmlOptions = $this->__getHtmlOptions($options);
		$htmlOptions['value'] = $title;

		if (!isset($htmlOptions['id'])) {
			$htmlOptions['id'] = 'submit' . intval(rand());
		}

		$htmlOptions['onclick'] = "return false;";
		
	/*	if ( !isset($options['data']) ) {
			$options['data'] = "$('#{$htmlOptions['id']}').parents('form').formSerialize()";
		}*/
		return $this->Form->submit($title, $htmlOptions)
			. $this->Html->scriptBlock("jQuery('#{$htmlOptions['id']}').click( function() { " . $this->remoteFunction($options, "jQuery('#{$htmlOptions['id']}').parents('form').ajaxSubmit") . "; return false;});");
	}

/**
 * Returns form tag that will submit using Ajax.
 *
 * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular
 * reloading POST arrangement. Even though it's using Javascript to serialize the form elements,
 * the form submission will work just like a regular submission as viewed by the receiving side
 * (all elements available in params).  The options for defining callbacks is the same
 * as AjaxHelper::link().
 *
 * @param mixed $params Either a string identifying the form target, or an array of method
 *                      parameters, including:
 *                          - 'params' => Acts as the form target
 *                          - 'type' => 'post' or 'get'
 *                          - 'options' => An array containing all HTML and script options used to
 *                             generate the form tag and Ajax request.
 * @param array $type How form data is posted: 'get' or 'post'
 * @param array $options Callback/HTML options
 * @return string JavaScript/HTML code
 * @see AjaxHelper::link()
 */
	function form($params = null, $type = 'post', $options = array()) {
		$model = false;
		if (is_array($params)) {
			extract($params, EXTR_OVERWRITE);
		}

/*		if (empty($options['url'])) {
			$options['url'] = array('action' => $params);
		}
*/

		$htmlDefaults = array(
			'id' => 'form' . intval(mt_rand()),
//			'onsubmit'	=> "return false;",
			'type' => $type
		);
		$htmlOptions = $this->__getHtmlOptions($options, array('model', 'with'));
		$htmlOptions = array_merge($htmlDefaults, $htmlOptions);
		$htmlOptions['url'] = Set::extract($options, 'url');

		$defaults = array('model' => $model);
		$options = array_merge($defaults, $options);
		$form = $this->Form->create($options['model'], $htmlOptions);
		$script =  $this->Javascript->codeBlock("jQuery('#{$htmlOptions['id']}').submit( function() { " . $this->remoteFunction($options, "jQuery('#{$htmlOptions['id']}').ajaxSubmit") . "; return false;});");
		return $form . $script;
	}


	/**
 * Makes an Ajax In Place editor control.
 *
 * @param string $id DOM ID of input element
 * @param string $url Postback URL of saved data
 * @param array $options Array of options to control the editor, including ajaxOptions (see link).
 * @link          http://github.com/madrobby/scriptaculous/wikis/ajax-inplaceeditor
 */
	function editor($id, $url, $options = array()) {
		$this->Javascript->link('jquery/jquery.jeditable.mini', false);
		$url = $this->url($url);
		$options = $this->_optionsToString($options, array(
			'id', 'name', 'loadurl', 'type', 'data', 'style', 'callback', 'submitdata', 'method', 'rows', 'cols', 'width', 'loadtype', 'loaddata', 'onblur', 'cancel', 'submit', 'tooltip', 'placeholder', 
		));
		$options = $this->_buildOptions($options, $this->editorOptions);	
		$script = "jQuery('#{$id}').editable('{$url}', {$options});";
		/*$options['ajaxOptions'] = $this->__optionsForAjax($options);

		foreach ($this->ajaxOptions as $opt) {
			if (isset($options[$opt])) {
				unset($options[$opt]);
			}
		}

		if (isset($options['callback'])) {
			$options['callback'] = 'function(form, value) {' . $options['callback'] . '}';
		}

		$type = 'InPlaceEditor';
		if (isset($options['collection']) && is_array($options['collection'])) {
			$options['collection'] = $this->Javascript->object($options['collection']);
			$type = 'InPlaceCollectionEditor';
		}

		$var = '';
		if (isset($options['var'])) {
			$var = 'var ' . $options['var'] . ' = ';
			unset($options['var']);
		}

		$options = $this->_optionsToString($options, array(
			'okText', 'cancelText', 'savingText', 'formId', 'externalControl', 'highlightcolor',
			'highlightendcolor', 'savingClassName', 'formClassName', 'loadTextURL', 'loadingText',
			'clickToEditText', 'okControl', 'cancelControl'
		));
		$options = $this->_buildOptions($options, $this->editorOptions);
		$script = "{$var}new Ajax.{$type}('{$id}', '{$url}', {$options});";
		*/
		return $this->Javascript->codeBlock($script);
	}

	function observeField($field, $options = array()) {
		return $this->Javascript->codeBlock("jQuery('#{$field}').change( function() { " . $this->remoteFunction($options, "jQuery('#{$field}').parents('form').ajaxSubmit") . "; return false;});");
	}


/**
 * Creates an Ajax-updateable DIV element
 *
 * @param string $id options for javascript
 * @return string HTML code
 */
	function div($id, $options = array()) {
		if (env('HTTP_X_UPDATE') != null) {
			$this->Javascript->enabled = false;
			$divs = explode(' ', env('HTTP_X_UPDATE'));

			if (in_array($id, $divs)) {
				@ob_end_clean();
				ob_start();
				return '';
			}
		}
		$attr = $this->_parseAttributes(array_merge($options, array('id' => $id)));
		return $this->output(sprintf($this->Html->tags['blockstart'], $attr));
	}
/**
 * Closes an Ajax-updateable DIV element
 *
 * @param string $id The DOM ID of the element
 * @return string HTML code
 */
	function divEnd($id) {
		if (env('HTTP_X_UPDATE') != null) {
			$divs = explode(' ', env('HTTP_X_UPDATE'));
			if (in_array($id, $divs)) {
				$this->__ajaxBuffer[$id] = ob_get_contents();
				ob_end_clean();
				ob_start();
				return '';
			}
		}
		return $this->output($this->Html->tags['blockend']);
	}
/**
 * Detects Ajax requests
 *
 * @return boolean True if the current request is a Prototype Ajax update call
 */
	function isAjax() {
		return (isset($this->request->params['isAjax']) && $this->request->params['isAjax'] === true);
	}
/**
 * Private helper function for Javascript.
 *
 */
	function __optionsForAjax($options = array()) {
		if (isset($options['update'])) {
			$update = $options['update'];
			if (is_array($options['update'])) {
				$update = join(' ', $options['update']);
			}
			$options['beforeSend'] = "request.setRequestHeader('X-Update', '{$update}');";
			if ( !isset($options['success']) ) {
				$options['success'] = '';
			}
			$options['success'] = "jQuery('#{$options['update']}').html(data);".$options['success'];
		}

		if ( isset($options['url']) ) {
			$options['url'] = $this->url($options['url']);
		}

		if (isset($options['indicator'])) {
			if (isset($options['loading'])) {
				if (!empty($options['loading']) && substr(trim($options['loading']), -1, 1) != ';') {
					$options['loading'] .= '; ';
				}
				$options['loading'] .= "jQuery('#{$options['indicator']}').show();";
			} else {
				$options['loading'] = "jQuery('#{$options['indicator']}').show();";
			}
			if (isset($options['complete'])) {
				if (!empty($options['complete']) && substr(trim($options['complete']), -1, 1) != ';') {
					$options['complete'] .= '; ';
				}
				$options['complete'] .= "jQuery('#{$options['indicator']}').hide();";
			} else {
				$options['complete'] = "jQuery('#{$options['indicator']}').hide();";
			}
			unset($options['indicator']);
		}
                if ( isset($options['loading']) ) {
                        if ( !isset($options['beforeSend']) ) {
                                $options['beforeSend'] = '';
                        }
                        $options['beforeSend'] .= $options['loading'];
                        unset($options['loading']);
                }


		$options = am(array('async' => true, 'type' => 'post'), $options);
		$options = $this->_optionsToString($options, array('async', 'contentType', 'dataType', 'jsonp', 'password', 'type', 'url', 'username', 'target'));
		$jsOptions = $this->_buildCallbacks($options);
		$jsOptions = array_merge($jsOptions, array_intersect_key($options, array_flip(array( 'async', 'cache', 'contentType', 'data', 'dataType', 'global', 'isModified', 'jsonp', 'password', 'processData', 'timeout', 'type', 'url', 'username', 'target' ))));

/*		foreach ($options as $key => $value) {
			switch($key) {
				case 'type':
					$jsOptions['asynchronous'] = !empty(($value == 'synchronous')) ? 'false' : 'true';
				break;
				case 'evalScripts':
					$jsOptions['evalScripts'] = !empty($value) ? 'true' : 'false';
				break;
				case 'position':
					$jsOptions['insertion'] = "Insertion." . Inflector::camelize($options['position']);
				break;
				case 'with':
					$jsOptions['parameters'] = $options['with'];
				break;
				case 'form':
					$jsOptions['parameters'] = 'Form.serialize(this)';
				break;
				case 'requestHeaders':
					$keys = array();
					foreach ($value as $key => $val) {
						$keys[] = "'" . $key . "'";
						$keys[] = "'" . $val . "'";
					}
					$jsOptions['requestHeaders'] = '[' . join(', ', $keys) . ']';
				break;
			}
		}*/
		return $this->_buildOptions($jsOptions, $this->ajaxOptions);
	}
/**
 * Private Method to return a string of html options
 * option data as a JavaScript options hash.
 *
 * @param array $options	Options in the shape of keys and values
 * @param array $extra	Array of legal keys in this options context
 * @return array Array of html options
 * @access private
 */
	function __getHtmlOptions($options, $extra = array()) {
		foreach ($this->ajaxOptions as $key) {
			if (isset($options[$key])) {
				unset($options[$key]);
			}
		}

		foreach ($extra as $key) {
			if (isset($options[$key])) {
				unset($options[$key]);
			}
		}

		return $options;
	}
/**
 * Returns a string of JavaScript with the given option data as a JavaScript options hash.
 *
 * @param array $options	Options in the shape of keys and values
 * @param array $acceptable	Array of legal keys in this options context
 * @return string	String of Javascript array definition
 */
	function _buildOptions($options, $acceptable) {
		if (is_array($options)) {
			$out = array();

			foreach ($options as $k => $v) {
				if (in_array($k, $acceptable)) {
					if ($v === true) {
						$v = 'true';
					} elseif ($v === false) {
						$v = 'false';
					}
					$out[] = "$k:$v";
				}
			}

			$out = join(', ', $out);
			$out = '{' . $out . '}';
			return $out;
		} else {
			return false;
		}
	}
/**
 * Return Javascript text for callbacks.
 *
 * @param array $options Option array where a callback is specified
 * @return array Options with their callbacks properly set
 * @access protected
 */
	function _buildCallbacks($options) {
		$callbacks = array();

		foreach ($this->callbacks as $callback) {
			if (isset($options[$callback])) {
				$code = $options[$callback];
				switch($callback) {
					case 'beforeSend':
						$callbacks[$callback] = "function(request) {" . $code . "}";
						break;
					case 'complete':
						$callbacks[$callback] = "function(request, textStatus) {" . $code . "}";
						break;
					case 'error':
						$callbacks[$callback] = "function(request, textStatus, errorThrown) {" . $code . "}";
						break;
					case 'success':
						$callbacks[$callback] = "function(data, textStatus) {". $code ."}";
						break;
					case 'beforeSubmit':
						$callbacks[$callback] = "function() {". $code ."}";
						break;
					default:
						$callbacks[$callback] = "function(request) {" . $code . "}";
						break;
				}

			}
		}
		return $callbacks;
	}
/**
 * Returns a string of JavaScript with a string representation of given options array.
 *
 * @param array $options	Ajax options array
 * @param array $stringOpts	Options as strings in an array
 * @access private
 * @return array
 */
	function _optionsToString($options, $stringOpts = array()) {
		foreach ($stringOpts as $option) {
			if (isset($options[$option]) /*&& !empty($options[$option]) && is_string($options[$option]) && $options[$option][0] != "'"*/) {
				if ($options[$option] === true || $options[$option] === 'true') {
					$options[$option] = 'true';
				} elseif ($options[$option] === false || $options[$option] === 'false') {
					$options[$option] = 'false';
				} elseif ( strpos($options[$option], '{') === 0 ) {
				
				} elseif ( strpos($options[$option], 'function(') === 0 ) {
					
				} else {
					$options[$option] = "'{$options[$option]}'";
				}
			}
		}
		return $options;
	}
/**
 * Executed after a view has rendered, used to include bufferred code
 * blocks.
 *
 * @access public
 */
	function afterRender() {
		if (env('HTTP_X_UPDATE') != null && !empty($this->__ajaxBuffer)) {
			@ob_end_clean();

			$data = array();
			$divs = explode(' ', env('HTTP_X_UPDATE'));
			$keys = array_keys($this->__ajaxBuffer);

			if (count($divs) == 1 && in_array($divs[0], $keys)) {
				echo $this->__ajaxBuffer[$divs[0]];
			} else {
				foreach ($this->__ajaxBuffer as $key => $val) {
					if (in_array($key, $divs)) {
						$data[] = $key . ':"' . rawurlencode($val) . '"';
					}
				}
				$out  = 'var __ajaxUpdater__ = {' . join(", \n", $data) . '};' . "\n";
				$out .= 'for (n in __ajaxUpdater__) { if (typeof __ajaxUpdater__[n] == "string" && jQuery(n)) jQuery(\'#n\').html(unescape(decodeURIComponent(__ajaxUpdater__[n])));}';

				echo $this->Javascript->codeBlock($out, false);
			}
			$scripts = $this->Javascript->getCache();

			if (!empty($scripts)) {
				echo $this->Javascript->codeBlock($scripts, false);
			}
			exit();
		}
	}
}

?>