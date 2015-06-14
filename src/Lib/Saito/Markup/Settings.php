<?php

namespace Saito\Markup;

use Cake\Core\Configure;

class Settings
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
    public function __construct(array $settings)
    {
        $this->set($settings + $this->_defaults);
        Configure::write('Saito.Settings.Parser', $this);

        return $this;
    }

    /**
     * Add
     *
     * @param string $mixed $key
     * @param mixed $value value
     * @return void
     */
    public function add($mixed, $value = null)
    {
        if ($value === null) {
            $this->_settings = $mixed + $this->_settings;
        } else {
            $this->_settings[$mixed] = $value;
        }
    }

    /**
     * Get settings
     *
     * @param string $key key
     * @return mixed
     */
    public function get($key = null)
    {
        if (isset($this->_settings[$key])) {
            return $this->_settings[$key];
        }

        return $this->_settings;
    }

    /**
     * Set settings
     *
     * @param array $settings settings
     * @return void
     */
    public function set($settings)
    {
        $this->_settings = $settings;
    }
}
