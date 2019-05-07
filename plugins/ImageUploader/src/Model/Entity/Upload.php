<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Model\Entity;

use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\ORM\Entity;
use Cake\Utility\Text;

class Upload extends Entity
{
    /**
     * Mutator for "name" property
     *
     * @param string $text content for "text"
     * @return string
     */
    //@codingStandardsIgnoreStart
    public function _setName(string $text)
    {
        $parts = explode('.', $text);

        if (count($parts) < 2) {
            return Text::slug($text);
        }

        $ext = array_pop($parts);
        $text = Text::slug(implode('.', $parts), '_') . '.' . $ext;

        return $text;
    }

    /**
     * Virtual property getting a File to the upload
     *
     * @return File
     */
    public function _getFile(): File
    {
        $folderPath = rtrim(Configure::read('Saito.Settings.uploadDirectory'), DS) . DS;
        return new File($folderPath . $this->get('name'));
    }
}
