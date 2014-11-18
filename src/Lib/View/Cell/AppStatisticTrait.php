<?php

namespace App\Lib\View\Cell;

use Saito\RememberTrait;

trait AppStatisticTrait
{

    use RememberTrait;

    public function getUserOnline()
    {
        if (empty($this->UserOnline)) {
            $this->loadModel('UserOnline');
        }

        return $this->rememberStatic(
            'UserOnline',
            function () {
                return $this->UserOnline->getLoggedIn();
            }
        );
    }

    public function getNUserOnline()
    {
        return $this->rememberStatic(
            'NUserOnline',
            function () {
                return $this->getUserOnline()->count();
            }
        );
    }
}
