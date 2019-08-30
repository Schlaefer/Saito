<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Markup;

use Cake\Core\Configure;

class MarkupSettings
{
    protected $_defaults = [
        //= default values for app settings
        'quote_symbol' => '>',
        'smilies' => false,
        //= computed values
        'atBaseUrl' => 'users/name/', // base-URL for @ tags
        'hashBaseUrl' => 'entries/view/', // base-URL for # tags
    ];

    protected $_settings;

    /**
     * Constructor
     *
     * @param array $settings settings
     */
    public function __construct(array $settings = [])
    {
        $this->set($settings);
    }

    /**
     * Set all settings
     *
     * @param array $settings settings
     * @return self
     */
    public function set(array $settings): self
    {
        $this->_settings = $settings + $this->_defaults;

        return $this;
    }

    /**
     * Get settings
     *
     * @param string $key key
     * @return mixed
     */
    public function get(string $key)
    {
        if (isset($this->_settings[$key])) {
            return $this->_settings[$key];
        }

        return null;
    }

    /**
     * Gets settings as array
     *
     * BC for BBCode Parser Class. Should be refactored to be not necessary.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->_settings;
    }
}
