<?php

namespace Plugin\BbcodeParser\src\Lib\Processors;

class BbcodeProcessorCollection
{

    protected $_Processors = [];

    /**
     * Add processor to collection.
     *
     * @param BbcodeProcessor $Preprocessor processor
     * @param array $options options
     * @return void
     */
    public function add($Preprocessor, array $options = [])
    {
        $options += ['priority' => 1000];
        $this->_Processors[$options['priority']][] = $Preprocessor;
    }

    /**
     * Porcess processors in collection
     *
     * @param string $string string to process
     * @param array $options options
     *
     * @return string
     */
    public function process($string, array $options = [])
    {
        foreach ($this->_Processors as $priority) {
            foreach ($priority as $Preprocessor) {
                $string = $Preprocessor->process($string, $options);
            }
        }
        return $string;
    }
}
