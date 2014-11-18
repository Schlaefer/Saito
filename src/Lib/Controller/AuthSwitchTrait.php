<?php

namespace App\Lib\Controller;

trait AuthSwitchTrait {

    // @todo @bogus why? Mayby: hidden login popover with same field id?
    protected function passwordAuthSwitch($data) {
        $data['password'] = $data['user_password'];
        unset($data['user_password']);
        return $data;
    }

}
