<?php

namespace Stopwatch\Routing\Filter;

use Cake\Event\Event;
use Cake\Routing\DispatcherFilter;
use \Stopwatch\Lib\Stopwatch;

class StopwatchFilter extends DispatcherFilter
{

    /**
     * {@inheritDoc}
     */
    public function beforeDispatch(Event $event)
    {
        Stopwatch::init();
        Stopwatch::enable();
        Stopwatch::start(
            '----------------------- Dispatch -----------------------'
        );
    }
}
