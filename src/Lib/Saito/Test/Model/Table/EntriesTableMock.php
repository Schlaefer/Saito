<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test\Model\Table;

use App\Model\Table\EntriesTable;

class EntriesTableMock extends EntriesTable
{

    protected $_table = 'entries';

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->setEntityClass('Entry');
        parent::initialize($config);
    }

    /**
     * prepare markup
     *
     * @param string $string string
     * @return mixed
     */
    public function prepareMarkup($string)
    {
        return $string;
    }

    /**
     * setting
     *
     * @return mixed
     */
    public function setting()
    {
        return $this->getConfig('subject_maxlength');
    }
}
