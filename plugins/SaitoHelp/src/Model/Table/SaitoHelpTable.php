<?php

namespace SaitoHelp\Model\Table;

use SaitoHelp\ORM\Markdown;

class SaitoHelpTable extends Markdown
{
    /**
     * Must be implemented by a ORM\Table compatible class.
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'default';
    }
}
