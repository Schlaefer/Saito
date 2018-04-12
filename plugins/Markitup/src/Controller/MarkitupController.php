<?php

namespace Markitup\Controller;

class MarkitupController extends Controller
{
    public $components = null;
    public $helpers = ['Markitup.Markitup'];
    public $layout = 'ajax';
    public $uses = null;

    /**
     * Preview
     *
     * @param string $parser parser
     * @return void
     */
    public function preview($parser = '')
    {
        $content = $this->data;
        $this->set(compact('content', 'parser'));
    }
}
