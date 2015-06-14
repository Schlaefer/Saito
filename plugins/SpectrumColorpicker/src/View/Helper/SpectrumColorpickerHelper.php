<?php

namespace SpectrumColorpicker\View\Helper;

use Cake\View\Helper;

class SpectrumColorpickerHelper extends Helper
{
    use Helper\IdGeneratorTrait;

    public $helpers = ['Form', 'Html', 'Url'];

    protected $_included = false;

    /**
     * Get Spectrum picker
     *
     * @param string $field model field name
     * @param array $options picker options
     * @return string picker form HTML
     */
    public function input($field, array $options = [])
    {
        $defaults = [
            'color-picker' => [
                'allowEmpty' => true,
                'preferredFormat' => 'hex',
                'showButtons' => false,
                'showInput' => true,
            ],
            'text' => [
                'label' => false,
                'maxlength' => '7',
                'type' => 'text'
            ]
        ];

        foreach (['color-picker', 'text'] as $key) {
            if (empty($options[$key])) {
                $options[$key] = [];
            }
            $options[$key] += $defaults[$key];
        }

        $html = $this->Form->input($field, $options['text']);
        $this->_generateJs($field, $options['color-picker']);

        return $html;
    }

    /**
     * Generate JS for picker
     *
     * @param string $field model field name to derive id-HTML-tag
     * @param array $options Spectrum picker options
     * @return void
     */
    protected function _generateJs($field, array $options = [])
    {
        $this->_includeAssets();

        $id = $this->_domId($field);
        $options = json_encode($options);
        $js = "$(function() { $('input#{$id}').spectrum({$options}); });";
        $this->Html->scriptBlock($js, ['block' => 'script']);
    }

    /**
     * Include assets (CSS, JS)
     *
     * @return void
     */
    protected function _includeAssets()
    {
        if ($this->_included) {
            return;
        }
        $this->_included = true;
        $this->Html->script(
            'SpectrumColorpicker.spectrum.js',
            ['block' => 'script-head']
        );
        $this->Html->css(
            'SpectrumColorpicker.spectrum.css',
            ['block' => 'css']
        );
    }
}
