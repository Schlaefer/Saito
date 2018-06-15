<?php

namespace Saito\View\Cell;

use Cake\View\Cell;
use Saito\App\Registry;

abstract class SlidetabCell extends Cell
{
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $this->_prepareRendering();
        $string = parent::__toString();

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function display();

    /**
     * {@inheritDoc}
     */
    abstract protected function _getSlidetabId();

    /**
     * Prepare rendering.
     *
     * @return void
     */
    protected function _prepareRendering()
    {
        $CurrentUser = Registry::get('CU');
        $slidetabId = $this->_getSlidetabId();
        $this->set(compact('CurrentUser', 'slidetabId'));
    }
}
