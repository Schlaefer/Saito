<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Posting;

use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\ThreadNode\ThreadNodeInterface;
use Saito\Posting\UserPosting\UserPostingInterface;
use Saito\User\CurrentUser\CurrentUserInterface;

interface PostingInterface extends BasicPostingInterface, ThreadNodeInterface, UserPostingInterface
{
    /**
     * Set current user for UserPostingTrait
     *
     * @param CurrentUserInterface $CU The current user
     * @return mixed
     */
    public function withCurrentUser(CurrentUserInterface $CU);
}
