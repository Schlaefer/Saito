<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Lib;

use Cake\Filesystem\File;

/**
 * Storage for installer state
 */
class InstallerState
{
    /**
     * Checks the installer state
     *
     * @param string $state state to check agains
     * @return bool true if installer is in state $state
     */
    public static function check(string $state): bool
    {
        $file = self::getFile();
        if (!$file->exists()) {
            return false;
        }

        return $file->read() === $state;
    }

    /**
     * Resets the installer state
     *
     * @return void
     */
    public static function reset(): void
    {
        self::getFile()->delete();
    }

    /**
     * Sets the installer state
     *
     * @param string $state the state
     * @return void
     */
    public static function set(string $state): void
    {
        self::getFile()->write($state);
    }

    /**
     * Gets handle to state storage file
     *
     * The file is stored as file in writable directory. Cache isn't available
     * during the installation.
     *
     * @return File file handle
     * @throws \RuntimeException
     */
    private static function getFile(): File
    {
        if (empty(TMP)) {
            throw new \RuntimeException('TMP directory not available.', 1560524787);
        }

        return (new File(TMP . 'installer.state'));
    }
}
