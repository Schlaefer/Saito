<?php

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
