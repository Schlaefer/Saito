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

use Cake\Utility\Text;

/**
 * Configuration class for the uploader
 */
class UploaderConfig
{
    /** @var string key used for CakePHP upload cache */
    private const CACHE_KEY = 'uploadsThumbnails';

    /** @var int max upload size in bytes */
    private $defaultSize = 2000000;

    /** @var int max number of allowed uploads per user */
    private $maxNumberOfUploads = 10;

    /** @var array allowed mime types */
    private $types = [];

    /** @var int Default target size for resizing a type in bytes */
    private $defaultResize = 450000;

    /**
     * Set default max file size when resizing a type
     *
     * @param int $size Size in bytes
     * @return self
     */
    public function setDefaultMaxResize(int $size): self
    {
        $this->defaultResize = $size;

        return $this;
    }

    /**
     * Get max file size when resizing a type
     *
     * @return int Size in bytes
     */
    public function getMaxResize(): int
    {
        return $this->defaultResize;
    }

    /**
     * Sets max allowed uploads per user
     *
     * @param int $max Max uploads
     * @return self
     */
    public function setMaxNumberOfUploadsPerUser(int $max): self
    {
        $this->maxNumberOfUploads = $max;

        return $this;
    }

    /**
     * Gets max allowed uploads per user
     *
     * @return int max uploads per user
     */
    public function getMaxNumberOfUploadsPerUser(): int
    {
        return $this->maxNumberOfUploads;
    }

    /**
     * Set default max file size
     *
     * @param string|int $number bytes as int or string e.g. "3MB"
     * @return self
     */
    public function setDefaultMaxFileSize($number): self
    {
        if (is_string($number)) {
            $number = (int)Text::parseFileSize($number, $this->defaultSize);
        }
        if (!is_int($number)) {
            throw new \InvalidArgumentException(
                'Max upload size isn\'t a number.',
                1561364890
            );
        }

        $this->defaultSize = $number;

        return $this;
    }

    /**
     * Adds a new mime type which is allowed to be uploaded
     *
     * @param string $type mime type as in "image/jpeg"
     * @param string|int|null $size optional file size different from default file size
     * @return self
     */
    public function addType(string $type, $size = null): self
    {
        if ($size === null) {
            $size = $this->defaultSize;
        } elseif (is_string($size)) {
            $size = (int)Text::parseFileSize($size, $this->defaultSize);
        }
        if (!is_int($size)) {
            throw new \InvalidArgumentException(
                'Upload size isn\'t a number.',
                1561364891
            );
        }
        $this->types[$type] = ['size' => $size ?: $this->defaultSize];

        return $this;
    }

    /**
     * Gets all allowed file types as array
     *
     * @return array file types
     */
    public function getAllTypes(): array
    {
        return array_keys($this->types);
    }

    /**
     * Gets file size for a a mime type
     *
     * @param string $type mime-type
     * @return int file-size in bytes
     * @throws \RuntimeException if mime-type isn't set
     */
    public function getSize(string $type): int
    {
        if (!$this->hasType($type)) {
            throw new \RuntimeException(
                sprintf('Upload type %s not found.', $type),
                1561357996
            );
        }

        return $this->types[$type]['size'];
    }

    /**
     * Gets cache key
     *
     * @return string cache-key
     */
    public function getCacheKey(): string
    {
        return self::CACHE_KEY;
    }

    /**
     * Checks if a mime-type is registered
     *
     * @param string $type mime-type
     * @return bool is type registered
     */
    public function hasType(string $type): bool
    {
        return !empty($this->types[$type]);
    }
}
