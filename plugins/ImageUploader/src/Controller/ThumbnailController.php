<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Controller;

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Http\Response;
use claviska\SimpleImage;

/**
 * Thumbnail Controller
 *
 * Extends raw Controller for performance
 */
class ThumbnailController extends Controller
{
    public $autoRender = false;

    /**
     * Thumb Image Generator
     *
     * @return Response
     */
    public function thumb(): Response
    {
        $id = (int)$this->request->getParam('id');
        ['type' => $type, 'raw' => $raw] = Cache::remember((string)$id, function () use ($id) {
            $Uploads = $this->loadModel('ImageUploader.Uploads');
            $document = $Uploads->get($id);

            $type = $document->get('type');
            $file = $document->get('file');
            $raw = $file->read();

            if ($document->get('size') > 150000) {
                $raw = (new SimpleImage())
                    ->fromString($raw)
                    ->bestFit(300, 300)
                    ->toString();
            }

            return compact('raw', 'type');
        }, 'uploadsThumbnails');

        return $this->response
            ->withHeader('Content-Type', $type)
            ->withStringBody($raw)
            ->withCache('-1 minute', '+1 year');
    }
}
