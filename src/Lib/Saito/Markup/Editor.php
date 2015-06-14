<?php

namespace Saito\Markup;

use Cake\View\Helper;

class Editor
{

    protected $_Helper;

    /**
     * Constructor
     *
     * @param Helper $Helper helper
     */
    public function __construct(Helper $Helper)
    {
        $this->_Helper = $Helper;
    }

    /**
     * Get editor help.
     *
     * @return string HTML-escaped content
     */
    public function getEditorHelp()
    {
        return '';
    }

    /**
     * Get markup set.
     *
     * @return array
     */
    public function getMarkupSet()
    {
        return [];
    }
}
