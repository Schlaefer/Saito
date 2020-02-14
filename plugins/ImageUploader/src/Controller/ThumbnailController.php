<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Controller;

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Response;
use claviska\SimpleImage;
use Saito\Exception\SaitoForbiddenException;

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
     * @return \Cake\Http\Response
     */
    public function thumb(): Response
    {
        $id = (int)$this->request->getParam('id');
        ['hash' => $fingerprint, 'type' => $type, 'raw' => $raw] = Cache::remember((string)$id, function () use ($id) {
            $Uploads = $this->loadModel('ImageUploader.Uploads');
            $document = $Uploads->get($id);

            $hash = $document->get('hash');
            $type = $document->get('type');
            $file = $document->get('file');
            $raw = $file->read();

            if ($document->get('size') > 150000) {
                $raw = (new SimpleImage())
                    ->fromFile($file->path)
                    ->bestFit(300, 300)
                    ->toString();
            }

            return compact('hash', 'raw', 'type');
        }, Configure::read('Saito.Settings.uploader')->getCacheKey());

        $hash = (string)$this->request->getQuery('h');
        if ($hash !== $fingerprint) {
            throw new SaitoForbiddenException(
                "Attempt to access image-thumbnail $id."
            );
        }

        return $this->response
            ->withHeader('Content-Type', $type)
            ->withStringBody($raw)
            ->withCache('-1 minute', '+1 year');
    }
}
