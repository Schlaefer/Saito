<?php

namespace App\View\Helper;

use Cake\Cache\Cache;

class ShoutsHelper extends AppHelper
{

    public $helpers = ['Api.Api', 'Parser'];

    protected $_cacheKey = 'Saito.Shouts.prepared';

    /**
     * prepare
     *
     * @param array $shouts shouts
     * @return array|bool
     */
    public function prepare($shouts)
    {
        if ($shouts->count() === 0) {
            return [];
        }

        foreach ($shouts as $shout) {
            $lastId = $shout->get('id');
            break;
        }

        $cache = $this->_readCache($lastId);
        if ($cache) {
            return $cache;
        }

        $prepared = [];
        foreach ($shouts as $shout) {
            $prepared[] = [
                'id' => $shout->get('id'),
                'time' => $shout->get('time')->toIso8601String(),
                'text' => $shout->get('text'),
                'html' => $this->Parser->parse(
                    $shout->get('text'),
                    ['multimedia' => false, 'wrap' => false]
                ),
                'user_id' => $shout->get('user_id'),
                'user_name' => $shout->get('user')->get('username')
            ];
        }

        $this->_writeCache($prepared);

        return $prepared;
    }

    /**
     * read cache
     *
     * @param int $lastId last id
     * @return bool
     */
    protected function _readCache($lastId)
    {
        $cache = Cache::read($this->_cacheKey);
        if ($cache && $cache['lastId'] === $lastId) {
            return $cache['data'];
        }

        return false;
    }

    /**
     * Write cache
     *
     * @param array $prepared prepared
     * @return void
     */
    protected function _writeCache($prepared)
    {
        $lastId = $prepared[0]['id'];
        Cache::write(
            $this->_cacheKey,
            [
                'lastId' => $lastId,
                'data' => $prepared
            ]
        );
    }
}
