<?php

namespace Saito\User\CurrentUser;

use Saito\User\SaitoUser;

class CurrentUser extends SaitoUser implements CurrentUserInterface
{
    use CurrentuserTrait;
}
