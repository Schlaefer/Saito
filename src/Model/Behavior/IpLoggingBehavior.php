<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;

class IpLoggingBehavior extends Behavior
{
    /**
     * {@inheritDoc}
     */
    public function beforeSave(\Cake\Event\EventInterface $event, Entity $entity)
    {
        if (!$entity->isNew() || !Configure::read('Saito.Settings.store_ip')) {
            return;
        }
        $ip = env('REMOTE_ADDR');
        if (Configure::read('Saito.Settings.store_ip_anonymized')) {
            $ip = static::_anonymizeIp($ip);
        }
        $entity->set('ip', $ip);
    }

    /**
     * Rough and tough ip anonymizer
     *
     * @param string $ip IP-address
     * @return string
     */
    protected static function _anonymizeIp($ip)
    {
        $strlen = strlen($ip);
        if ($strlen > 6) {
            $divider = (int)floor($strlen / 4) + 1;
            $ip = substr_replace($ip, '…', $divider, $strlen - (2 * $divider));
        }

        return $ip;
    }
}
