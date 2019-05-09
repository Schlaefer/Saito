<?php
	// @codingStandardsIgnoreFile
	App::uses('AppHelper', 'View/Helper');
/**
 *    Farbtastic helper
 *    @author      	Curtis Gibby
 *    @author     	Sebastian Siedentopf
 *    @desc        This helper does everything you need related to Farbtastic within CakePHP
 *
 *                Like Farbtastic, requires jQuery to function properly.
 *                jQuery: http://jquery.com
 *
 *                Also requires a Color Wheel icon (color.png in this example)
 *                like the one from Mark James' Silk set
 *                http://www.iconfinder.net/icondetails/5185/16/
 *
 *    @version    2010-07-18
 * 		@td make plugin, fork from http://bakery.cakephp.org/articles/view/helper-for-farbtastic-jquery-color-picker to githup
 */
class FarbtasticHelper extends Helper  {

    public $helpers = ['Form', 'Html'];

		protected $output_files =	true;


    /**
    *    Generate a form input and related div and icon
    *
    *    may need to customize $icon_file (relative to webroot)
    *
    *    Adapted from April Hodge Silver's "Simple Colorpicker" input function
    *    http://bakery.cakephp.org/articles/view/simple-colorpicker
    */
    function input($name, $label='') {

        $icon_file = '../../js/farbtastic/color.png'; // update to wherever your icon is.

        list($model, $fieldname) = explode('.', $name);
        if (empty($label)) {
            $label = Inflector::Humanize($fieldname);
        }

        if(isset($this->request->data[$model][$fieldname])) {
            $color_value = str_replace("#", "", $this->request->data[$model][$fieldname]); // expects an RGB string, strips any incoming '#' character
        }
        else {
            $color_value = "000000"; // black
        }

        $str = '';
        $str .= '<div class="input text colorpicker">';
				$str .= $this->Form->input($fieldname, [
					'id' => $model . Inflector::Camelize($fieldname),
					'class' => 'farbtastic-input',
					'maxlength' => 7,
					'label' => $label,
					'value' => "#$color_value"
				]);
        $str .= '<img id="farbtastic-picker-icon-'.Inflector::Camelize($fieldname).'" src="'.$icon_file.'" alt="Color Picker" title="Color Picker" class="farbtastic-picker-icon">';
        $str .= '<div style="display:none;" class="farbtastic-picker" id="farbtastic-picker-'.Inflector::Camelize($fieldname).'"></div>';
        $str .= '</div>';

				$this->readyJS($name);

				### include css and js files
				if ($this->output_files) {
					$this->Html->script('farbtastic/farbtastic', array('inline' => false));
					$this->Html->css('../js/farbtastic/farbtastic', null, array('inline' => false));
				}
				$this->output_files = false;

        return $str;
    }



    /**
    *    Add the jQuery magic to the $(document).ready function
    *    Farbtastic-ize the input, make the button show/hide the color picker div
    */
    function readyJS($name) {
        list($model,$fieldname) = explode('.',$name);
        $str = '$(document).ready(function() { ';
        $str .= ' $("#farbtastic-picker-'.Inflector::Camelize($fieldname).'").farbtastic("#'.$model.Inflector::Camelize($fieldname).'"); ';
        $str .= ' $("#farbtastic-picker-icon-'.Inflector::Camelize($fieldname).'").click( function() { $("#farbtastic-picker-'.Inflector::Camelize($fieldname).'").toggle("slow"); }); ';
        $str .= '})';
				$this->Html->scriptBlock($str, array('inline'=>false));
    }
}?>