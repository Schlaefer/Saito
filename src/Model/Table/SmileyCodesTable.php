<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppSettingTable;

/**
 * @property SmiliesTable $Smilies
 */
class SmileyCodesTable extends AppSettingTable
{

    public $name = 'SmileyCode';

    public $displayField = 'code';

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        $this->belongsTo('Smilies', ['foreignKey' => 'smiley_id']);
    }
}
