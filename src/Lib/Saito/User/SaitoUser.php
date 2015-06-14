<?php

namespace Saito\User;

/**
 * Class SaitoUser
 */
class SaitoUser implements ForumsUserInterface
{
    use SaitoUserTrait;

    public $Categories;

    /**
     * Constructor.
     *
     * @param mixed $settings user-settings
     */
    public function __construct($settings = null)
    {
        if ($settings !== null) {
            $this->setSettings($settings);
            $this->Categories = new Categories($this);
        }
    }
}
