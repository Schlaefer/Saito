<?php

namespace Saito\User\CurrentUser;

use Saito\User\ForumsUserInterface;

interface CurrentUserInterface extends ForumsUserInterface
{
    /**
     * Has current-user bookmarked posting
     *
     * @param int $postingId posting-ID
     * @return bool
     */
    public function hasBookmarked($postingId);

    /**
     * checks if current user ignores user or get all ignored users
     *
     * @param null|int $userId user-ID
     * @return bool|array
     */
    public function ignores($userId);
}
