<?php

namespace App\Lib\Model\Table;

use App\Lib\Model\Table\AppTable;
use Cake\Event\Event;
use Cake\ORM\Entity;

class AppSettingTable extends AppTable
{

    /**
     * afterSave callback
     *
     * @param Event $event event
     * @param Entity $entity entity
     * @param array|\ArrayObject $options options
     *  - 'clearCache' set to 'false' to prevent cache clearing
     * @return void
     */
    public function afterSave(Event $event, Entity $entity, \ArrayObject $options)
    {
        if (!isset($options['clearCache']) || $options['clearCache'] !== false) {
            $this->clearCache();
        }
    }

    /**
     * After delete
     *
     * @return void
     */
    public function afterDelete()
    {
        $this->clearCache();
    }

    /**
     * Clear cache
     *
     * @return void
     */
    public function clearCache()
    {
        $this->_dispatchEvent(
            'Cmd.Cache.clear',
            ['cache' => ['Saito', 'Thread']]
        );
    }
}
