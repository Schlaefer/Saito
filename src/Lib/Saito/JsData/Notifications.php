<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\JsData;

class Notifications
{
    protected $notifications = [];

    /**
     * Add message
     *
     * @param string $message message
     * @param array|null $options options
     * @return void
     */
    public function add(string $message, ?array $options = []): void
    {
        $defaults = [
            'type' => 'notice',
            'channel' => 'notification'
        ];
        $options = array_merge($defaults, $options);

        if (!is_array($message)) {
            $message = [$message];
        }

        foreach ($message as $m) {
            $nm = [
                'message' => $m,
                'type' => $options['type'],
                'channel' => $options['channel']
            ];
            if (isset($options['title'])) {
                $nm['title'] = $options['title'];
            }
            if (isset($options['element'])) {
                $nm['element'] = $options['element'];
            }
            $this->notifications[] = $nm;
        }
    }

    /**
     * Gets all messages
     *
     * @return array All messages.
     */
    public function getAll(): array
    {
        return $this->notifications;
    }
}
