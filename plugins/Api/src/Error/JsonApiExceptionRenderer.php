<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Api\Error;

use Api\Error\Exception\GenericApiException;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Error\ExceptionRenderer;
use Cake\View\View;

class JsonApiExceptionRenderer extends ExceptionRenderer
{
    /**
     * {@inheritDoc}
     *
     * @see https://stackoverflow.com/questions/40327079/how-to-change-error-response-structure-for-json-request-cakephp-3
     * @see http://jsonapi.org/format/#errors
     */
    protected function _outputMessage($template)
    {
        $data = [
            'errors' => [
                [
                    'title' => $this->controller->viewVars['message'],
                    'code' => $this->controller->viewVars['code']
                ]
            ]
        ];

        if (Configure::read('debug')) {
            $data += $this->controller->viewVars;
        }

        $this->controller->set('data', $data);
        $this->controller->set('_serialize', 'data');

        return parent::_outputMessage($template);
    }
}
