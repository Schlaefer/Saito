<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
