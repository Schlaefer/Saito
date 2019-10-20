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

use Cake\Core\Configure;
use Cake\Log\Log;
use Stopwatch\Lib\Stopwatch;

class CronJob
{

    /**
     * @var string
     */
    private $uid;

    /**
     * @var int
     */
    private $due;

    /**
     * @var callable
     */
    private $func;

    /**
     * Constructor
     *
     * @param string $uid unique ID for cron job
     * @param string $due due intervall
     * @param callable $func cron job
     */
    public function __construct(string $uid, string $due, callable $func)
    {
        $this->uid = $uid;
        $this->due = strtotime($due);
        if (!$this->due) {
            throw new \InvalidArgumentException(
                sprintf('Cannot convert "%s" into a timestamp.', $due),
                1571567221
            );
        }
        $this->func = $func;
    }

    /**
     * Get the value of uid
     *
     * @return  string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * When should the job be next executed
     *
     * @return int UNIX-timestamp
     */
    public function getDue(): int
    {
        return $this->due;
    }

    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute(): void
    {
        $msg = 'Cron.CronJob::execute ' . $this->getUid();
        if (Configure::read('Saito.debug.logInfo')) {
            Log::write('info', $msg, ['scope' => ['saito.info']]);
        }

        Stopwatch::start($msg);
        call_user_func($this->func);
        Stopwatch::stop($msg);
    }
}
