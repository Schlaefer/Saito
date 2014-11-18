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
     * @param Event $event
     * @param Entity $entity
     * @param array|\ArrayObject $options
     *  - 'clearCache' set to 'false' to prevent cache clearing
     */
    public function afterSave(
        Event $event,
        Entity $entity,
        \ArrayObject $options
    ) {
        if (!isset($options['clearCache']) || $options['clearCache'] !== false) {
            $this->clearCache();
        }
    }

    public function afterDelete()
    {
        $this->clearCache();
    }

    public function clearCache()
    {
        $this->_dispatchEvent(
            'Cmd.Cache.clear',
            ['cache' => ['Saito', 'Thread']]
        );
    }

}
