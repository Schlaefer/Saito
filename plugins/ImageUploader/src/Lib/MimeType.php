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

use Cake\Core\Configure;
use Cake\Filesystem\File;

/**
 * Determine mime-type for a file and try to fix some common mime-type issues.
 */
class MimeType
{
    /** @var array [<wrong type> => [<file .ext> => <right type>]] */
    private static $conversion = [
        'application/octet-stream' => [
            'mp4' => 'video/mp4',
        ],
    ];

    /**
     * Get mime-type
     *
     * @param string $filepath File path on server to check the actual file
     * @param string|null $name Original file name with original extension
     * @return string Determined mime-type
     */
    public static function get(string $filepath, ?string $name): string
    {
        $file = new File($filepath);
        $type = $file->mime();

        $name = $name ?: $file->pwd();
        $type = self::fixByFileExtension($type, $name);

        return $type;
    }

    /**
     * Fix type based on filename extension
     *
     * @param string $type original mime-type
     * @param string $filename path to file for filename
     * @return string fixed mime-type
     */
    private static function fixByFileExtension(string $type, string $filename): string
    {
        // Check that mime-type has an .extension based fix.
        if (array_key_exists($type, self::$conversion)) {
            $UploaderConfig = Configure::read('Saito.Settings.uploader');
            $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $conversion = self::$conversion[$type];
            foreach ($conversion as $extension => $newType) {
                // Check that file has the matching .extension.
                if ($fileExtension !== $extension) {
                    continue;
                }
                // Check that the mime-type wich is considered a fix is allowed
                // at all.
                if (!$UploaderConfig->hasType($newType)) {
                    continue;
                }
                $type = $newType;
                break;
            }
        }

        return $type;
    }
}
