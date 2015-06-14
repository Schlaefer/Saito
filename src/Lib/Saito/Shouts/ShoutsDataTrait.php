<?php

namespace Saito\Shouts;

use App\Model\Table\ShoutsTable;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Saito\RememberTrait;

trait ShoutsDataTrait
{

    use RememberTrait;

    /**
     * Get
     *
     * @return array|\Cake\Datasource\EntityInterface|mixed
     */
    public function getShouts()
    {
        return $this->_table()->get();
    }

    /**
     * Push
     *
     * @param array $data data
     * @return bool|\Cake\Datasource\EntityInterface|mixed
     */
    public function pushShout($data)
    {
        return $this->_table()->push($data);
    }

    /**
     * get model
     *
     * @return ShoutsTable|\Cake\ORM\Table
     */
    protected function _table()
    {
        return $this->remember('model', function () {
            $Table = TableRegistry::get('Shouts');
            $Table->maxNumberOfShouts = (int)Configure::read(
                'Saito.Settings.shoutbox_max_shouts'
            );
            return $Table;
        });
    }
}
