<?php

namespace App\Lib\Controller;

trait AuthSwitchTrait
{

    /**
     * AuthSwitch
     *
     * @todo @bogus why? Mayby: hidden login popover with same field id?
     *
     * @param array $data data
     * @return mixed
     */
    protected function passwordAuthSwitch($data)
    {
        $data['password'] = $data['user_password'];
        unset($data['user_password']);

        return $data;
    }
}
