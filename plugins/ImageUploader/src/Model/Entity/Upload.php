<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Model\Entity;

use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\ORM\Entity;
use Cake\Utility\Text;

/**
 * Upload entity
 *
 * @property User $user
 */
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
     * @return File handle to the file
     */
    public function _getFile(): File
    {
        $folderPath = rtrim(Configure::read('Saito.Settings.uploadDirectory'), DS) . DS;
        return new File($folderPath . $this->get('name'));
    }

    /**
     * Generates hash for the file
     *
     * Isn't secure or unique; performance preferred.
     *
     * @return string hash
     */
    public function _getHash(): string
    {
        return md5($this->get('name'));
    }
}
