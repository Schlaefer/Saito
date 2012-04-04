<?php

	App::uses('AppHelper', 'View/Helper');
	App::uses('HtmlHelper', 'View/Helper');
	App::uses('FormHelper', 'View/Helper');

class MarkitupHelper extends AppHelper {
	public $helpers = array('Html', 'Form');
	public $paths = array(
		'css' => '/markitup/js/markitup/',
		'js' => '/markitup/js/markitup/',
	);
	public $vendors = array('markdown' => 'Markitup.Markdown');
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		
		$paths = Configure::read('Markitup.paths');
		if (empty($paths)) {
			return;
		}

		if (is_string($paths)) {
			$paths = array('js' => $paths);
		}

		$this->paths = array_merge($this->paths, $paths);
	}
	/**
	 * Generates a form textarea element complete with label and wrapper div with markItUp! applied.
	 *
	 * @param string $fieldName This should be "Modelname.fieldname"
	 * @param array $settings
	 * @return string  An <textarea /> element.
	 */
	public function editor($name, $settings = array()) {
		echo $this->Html->script($this->paths['js'] . 'jquery.markitup', array('inline' => true));
		$config = $this->_build($settings);
		$settings = $config['settings'];
		$default = $config['default'];
		$textarea = array_diff_key($settings, $default);
		$textarea = array_merge($textarea, array('type' => 'textarea'));
		$id = '#' . $this->Form->domId($name);

//		$out[] = 'jQuery.noConflict();';
		$out[] = 'jQuery(function() {';
		$out[] = '  jQuery("' . $id . '").markItUp(';
		$out[] = '     ' . $settings['settings'] . ',';
		$out[] = '     {';
		$out[] = '        previewParserPath:"' . $settings['parser'] . '"';
		$out[] = '     }';
		$out[] = '  );';
		$out[] = '});';
		return $this->output($this->Form->input($name, $textarea) . $this->Html->scriptBlock(join("\n", $out)));
	}
	/**
	 * Link to build markItUp! on a existing textfield
	 *
	 * @param string $title The content to be wrapped by <a> tags.
	 * @param string $fieldName This should be "Modelname.fieldname" or specific domId as #id.
	 * @param array  $settings
	 * @param array  $htmlAttributes Array of HTML attributes.
	 * @param string $confirmMessage JavaScript confirmation message.
	 * @return string An <a /> element.
	 */
	public function create($title, $fieldName = "", $settings = array(), $htmlAttributes = array(), $confirmMessage = false) {
		$id = ($fieldName{0} === '#') ? $fieldName : '#'.parent::domId($fieldName);

		$config = $this->_build($settings);
		$settings = $config['settings'];
		$htmlAttributes = array_merge($htmlAttributes, array('onclick' => 'jQuery("'.$id.'").markItUpRemove(); jQuery("'.$id.'").markItUp('.$settings['settings'].', { previewParserPath:"'.$settings['parser'].'" }); return false;'));
		return $this->Html->link($title, "#", $htmlAttributes, $confirmMessage, false);
	}

	/**
	 * Link to destroy a markItUp! editor from a textfield
	 * @param string  $title The content to be wrapped by <a> tags.
	 * @param string  $fieldName This should be "Modelname.fieldname" or specific domId as #id.
	 * @param array   $htmlAttributes Array of HTML attributes.
	 * @param string  $confirmMessage JavaScript confirmation message.
	 * @return string An <a /> element.
	 */
	public function destroy($title, $fieldName = "", $htmlAttributes = array(), $confirmMessage = false) {
		$id = ($fieldName{0} === '#') ? $fieldName : '#'.parent::domId($fieldName);
		$htmlAttributes = array_merge($htmlAttributes, array('onclick' => 'jQuery("'.$id.'").markItUpRemove(); return false;'));
		return $this->Html->link($title, "#", $htmlAttributes, $confirmMessage, false);
	}

	/**
	 * Link to add content to the focused textarea
	 * @param string  $title The content to be wrapped by <a> tags.
	 * @param string  $fieldName This should be "Modelname.fieldname" or specific domId as #id.
	 * @param mixed   $content String or array of markItUp! options (openWith, closeWith, replaceWith, placeHolder and more. See markItUp! documentation for more details : http://markitup.jaysalvat.com/documentation
	 * @param array   $htmlAttributes Array of HTML attributes.
	 * @param string  $confirmMessage JavaScript confirmation message.
	 * @return string An <a /> element.
	 */
	public function insert($title, $fieldName = null, $content = array(), $htmlAttributes = array(), $confirmMessage = false) {
		if (isset($fieldName)) {
			$content['target'] = ($fieldName{0} === '#') ? $fieldName : '#'.parent::domId($fieldName);
		}
		if (!is_array($content)) {
			$content['replaceWith'] = $content;
		}
		$properties = '';
		foreach($content as $k => $v) {
			$properties .= $k.':"'.addslashes($v).'",';
		}
		$properties = substr($properties, 0, -1);

		$htmlAttributes = array_merge($htmlAttributes, array('onclick' => '$.markItUp( { '.$properties.' } ); return false;'));
		return $this->Html->link($title, "#", $htmlAttributes, $confirmMessage, false);
	}

	public function parse($content, $parser = 'default') {
		$parsers = Configure::read('Markitup.vendors');
		if (empty($vendors)) {
			$vendors = array();
		}
		$vendors = array_merge($this->vendors, $vendors);

		if (array_key_exists($parser, $vendors)) {
			if (!is_array($vendors[$parser])) {
				$vendors[$parser] = array('class' => $vendors[$parser]);
			}
			extract($vendors[$parser]);
			$plugin = 'App';
			if (strpos($class, '.')) {
				list($plugin, $class) = explode('.', $class);
			}
			if (!isset($file)) {
				$file = null;
			}
	      App::import('Vendor', $plugin . '.' . $class, null, null, $file);
			$content = $class($content);
		}

		echo $this->Html->css($this->paths['css'] . 'templates' . DS . 'preview', null, array('inline' => false));

		return $content;
	}
	protected function _build($settings) {
		$default = array(
			'set' => 'default',
			'skin' => 'simple',
			'settings' => 'mySettings',
			'parser' => array(
				'plugin' => 'markitup',
				'controller' => 'markitup',
				'action' => 'preview',
				'admin' => false,
			)
		);
		$settings = array_merge($default, $settings);
		if ($settings['parser']) {
			$settings['parser'] = $this->Html->url(Router::url(array_merge($settings['parser'], array($settings['set']))));
		}

		echo $this->Html->css(array(
			$this->paths['css'] . 'skins' . DS . $settings['skin'] . DS . 'style',
			$this->paths['css'] . 'sets' . DS . $settings['set'] . DS . 'style',
		), null, array('inline' => true));

		echo $this->Html->script($this->paths['js'] . 'sets' . DS . $settings['set'] . DS . 'set', true);

		return array('settings' => $settings, 'default' => $default);
	}
}
?>