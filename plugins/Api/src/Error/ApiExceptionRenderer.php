<?php

namespace Api\Error;

use Api\Error\Exception\GenericApiException;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Error\ExceptionRenderer;

class ApiExceptionRenderer extends ExceptionRenderer
{

    /**
     * {@inheritdoc}
     *
     * @return \Cake\Network\Response
     */
    public function render()
    {
        if (!Configure::read('debug') && $this->error instanceof Exception) {
            $this->error = new GenericApiException(
                $this->error->getMessage()
            );
        }

        return parent::render();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Exception $exception Exception instance.
     * @param string $method Method name
     * @param int $code Error code
     * @return string Template name
     */
    protected function _template(\Exception $exception, $method, $code)
    {
        return $this->template = 'apierror';
    }
}
