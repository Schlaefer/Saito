<?php

namespace Saito\Test;

use Cake\Error\ExceptionRenderer as CakeExceptionRenderer;

class ExceptionRenderer extends CakeExceptionRenderer
{

    /**
     * {@inheritDoc}
     */
    public function __construct(\Exception $exception)
    {
        throw $exception;
    }
}
