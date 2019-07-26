<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Cell;

use Cake\View\Cell;
use Saito\App\Registry;

/**
 * AppStatus cell
 */
class AppStatusCell extends Cell
{

    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array
     */
    protected $_validCellOptions = [];

    /**
     * {@inheritDoc}
     */
    public function display()
    {
        $this->set('CurrentUser', Registry::get('CU'));
        $this->set('Stats', Registry::get('AppStats'));
    }
}
