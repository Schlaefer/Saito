<?php

namespace Saito\Test\Model\Table;

use App\Model\Table\EntriesTable;

class AppTableMock extends EntriesTable
{

    /**
     * set allowed input fields
     *
     * @param array $in array
     * @return void
     */
    public function setAllowedInputFields($in)
    {
        $this->allowedInputFields = $in;
    }
}
