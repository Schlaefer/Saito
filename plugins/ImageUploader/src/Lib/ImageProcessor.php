<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Lib;

use claviska\SimpleImage;

/**
 * Determine mime-type for a file and try to fix some common mime-type issues.
 */
class ImageProcessor
{
    /**
     * Resizes a file
     *
     * @param \ImageUploader\Lib\File $file File
     * @param int $target Target size
     * @return void
     */
    public static function resize(File $file, int $target): void
    {
        if ($file->getSize() < $target) {
            return;
        }

        $raw = $file->read();

        [$width, $height] = getimagesizefromstring($raw);
        $ratio = $file->getSize() / $target;
        $ratio = sqrt($ratio);

        $newwidth = (int)($width / $ratio);
        $newheight = (int)($height / $ratio);
        $destination = imagecreatetruecolor($newwidth, $newheight);

        $source = imagecreatefromstring($raw);
        imagecopyresized($destination, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        $raw = $destination;

        $type = $file->getMime();
        switch ($type) {
            case 'image/jpeg':
                imagejpeg($destination, $file->getPath());
                break;
            case 'image/png':
                imagepng($destination, $file->getPath());
                break;
            default:
                throw new \RuntimeException();
        }
    }

    /**
     * Fix image orientation according to image exif data
     *
     * @param string $path Path to file
     * @return void
     */
    public static function fixOrientation(string $path): void
    {
        try {
            (new SimpleImage())
                ->fromFile($path)
                ->autoOrient()
                ->toFile($path, null, 75);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Fixing orientation failed.');
        }
    }

    /**
     * Convert image file to jpeg
     *
     * @param string $path Path to file
     * @return void
     */
    public static function convertToJpeg(string $path): void
    {
        try {
            (new SimpleImage())
                ->fromFile($path)
                ->toFile($path, 'image/jpeg', 75);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Converting file to jpeg failed.');
        }
    }
}
