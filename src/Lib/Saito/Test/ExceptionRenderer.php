<?php

    namespace Saito\Test;

    use Cake\Error\ExceptionRenderer as CakeExceptionRenderer;

    class ExceptionRenderer extends CakeExceptionRenderer {

        public function __construct(\Exception $exception)
        {
            throw $exception;
        }

    }
