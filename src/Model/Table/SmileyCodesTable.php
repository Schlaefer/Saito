<?php

namespace App\Model\Table;

use App\Lib\Model\Table\AppSettingTable;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Entity;

class SmileyCodesTable extends AppSettingTable
{

    public $name = 'SmileyCode';

    public $displayField = 'code';

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->belongsTo('Smilies', ['foreignKey' => 'smiley_id']);
    }
}
