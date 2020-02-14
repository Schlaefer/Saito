<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\View\Helper;

use App\View\Helper\AppHelper;
use ImageUploader\Model\Entity\Upload;

/**
 * Image Uploader Helper
 *
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class ImageUploaderHelper extends AppHelper
{
    public $helpers = ['Url'];

    /**
     * Returns data representation for an image
     *
     * @param \ImageUploader\Model\Entity\Upload $image image
     * @return array
     */
    public function image(Upload $image): array
    {
        return [
            'id' => $image->get('id'),
            'type' => 'uploads',
            'attributes' => [
                'id' => $image->get('id'),
                'created' => $image->get('created'),
                'mime' => $image->get('type'),
                'name' => $image->get('name'),
                'title' => $image->get('title'),
                'size' => $image->get('size'),
                'url' => $this->Url->assetUrl(
                    'useruploads/' . $image->get('name'),
                    ['fullBase' => true]
                ),
                'thumbnail_url' => $this->Url->build(
                    [
                        '_name' => 'imageUploader-thumbnail',
                        'id' => $image->get('id'),
                        '?' => ['h' => $image->get('hash')],
                    ],
                    ['fullBase' => true]
                ),
            ],

        ];
    }
}
