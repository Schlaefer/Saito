<?php

namespace Api\View\Helper;

use Cake\View\Helper;

class ApiHelper extends Helper
{

    public $helpers = ['TimeH'];

    /**
     * Convert timestamp from DB to JSON format
     *
     * @param string|Carbon $date date to convert
     * @return string date in JSON format
     */
    public function mysqlTimestampToIso($date)
    {
        if (empty($date)) {
            $date = 0;
        }

        return $this->TimeH->mysqlTimestampToIso($date);
    }
}
