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

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

class File
{
    /**
     * Path to file
     * @var string
     */
    protected string $path;

    /**
     * Filesystem
     * @var \League\Flysystem\FilesystemInterface
     */
    protected FilesystemInterface $fs;

    /**
     * Constructor
     * @param string $path path to file
     * @param null|\League\Flysystem\FilesystemInterface $filesystem filesystem (default: local from path)
     * @return void
     */
    public function __construct(string $path, ?FilesystemInterface $filesystem = null)
    {
        $this->path = $path;
        $this->fs = $filesystem ?: new Filesystem(new Local($this->getDirname()));
    }

    /**
     * Get Filesystem
     * @return \League\Flysystem\FilesystemInterface
     */
    public function getFs(): FilesystemInterface
    {
        return $this->fs;
    }

    /**
     * Get dirname of path
     * @return string dirname
     */
    public function getDirname(): string
    {
        return dirname($this->getPath());
    }

    /**
     * Path getter
     * @return string path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get mime type from physical file
     * @return string Mime Type
     */
    public function getMime(): string
    {
        return $this->fs->getMimetype($this->getBasename());
    }

    /**
     * Get size from storage
     * @return int size
     */
    public function getSize(): int
    {
        return $this->fs->getSize($this->getBasename());
    }

    /**
     * Get basename of path
     * @return string basename
     */
    public function getBasename(): string
    {
        return basename($this->getPath());
    }

    /**
     * Read content from storage
     * @return string
     */
    public function read(): string
    {
        return $this->fs->read($this->getBasename());
    }

    /**
     * Check if item exists in storage
     * @return bool
     */
    public function exists(): bool
    {
        return $this->fs->has($this->getBasename());
    }

    /**
     * Delete a file in path
     * @return bool True on success, False otherwise
     */
    public function delete(): bool
    {
        return $this->fs->delete($this->getBasename());
    }
}
