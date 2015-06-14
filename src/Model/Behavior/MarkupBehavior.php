<?php

namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Saito\Markup\Preprocessor;

class MarkupBehavior extends Behavior
{

    /**
     * @var Preprocessor
     */
    protected $_Preprocessor;

    /**
     * prepare markup
     *
     * @param string $string string
     * @return string
     */
    public function prepareMarkup($string)
    {
        if (empty($string)) {
            return $string;
        }
        return $this->_getPreprocessor()->process($string);
    }

    /**
     * get preprocessor
     *
     * @return Preprocessor
     */
    protected function _getPreprocessor()
    {
        if ($this->_Preprocessor === null) {
            $settings = Configure::read('Saito.Settings.Parser');
            $this->_Preprocessor = \Saito\Plugin::getParserClassInstance(
                'Preprocessor',
                $settings
            );
        }
        return $this->_Preprocessor;
    }
}
