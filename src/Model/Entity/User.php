<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Saito\User\ForumsUserInterface;
use Saito\User\ForumsUserTrait;

class User extends Entity implements ForumsUserInterface
{
    use ForumsUserTrait;
}
