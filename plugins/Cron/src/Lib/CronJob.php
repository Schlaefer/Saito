<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Cron\Lib;

class CronJob
{

    /**
     * @var string
     */
    public $uid;

    /**
     * @var mixed
     */
    public $due;

    /**
     * @var callable
     */
    protected $_garbage;

    /**
     * Constructor
     *
     * @param string $uid unique ID for cron job
     * @param mixed $due due intervall
     * @param callable $func cron job
     */
    public function __construct($uid, $due, callable $func)
    {
        $this->uid = $uid;
        $this->due = $due;
        $this->_garbage = $func;
    }

    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute()
    {
        call_user_func($this->_garbage);
    }
}
