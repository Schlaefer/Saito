<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\View\Cell;

use Cake\View\Cell;
use Saito\User\CurrentUser\CurrentUserInterface;

abstract class SlidetabCell extends Cell
{
    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        $this->_prepareRendering();
        $string = parent::__toString();

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function display(CurrentUserInterface $CurrentUser);

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
        $slidetabId = $this->_getSlidetabId();
        $this->set(compact('slidetabId'));
    }
}
