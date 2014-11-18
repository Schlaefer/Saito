<?php

namespace App\Model\Table;

use App\Lib\Model\Table\AppTable;

class UploadsTable extends AppTable
{

    // @todo 3.0
    public $actsAs = array('FileUpload.FileUpload');

    public function initialize(array $config)
    {
        $this->belongsTo('Users', ['foreignKey' => 'smiley_id']);
    }

    public function deleteAllFromUser($userId)
    {
        return $this->deleteAll(
            [
                'Upload.user_id' => $userId
            ]
            // @todo 3.0
            // call beforeDelete FileUploader plugin callback to remove files from disk
//           , true
        );
    }

    /**
     * Returns the number of uploads a user `user_id` has made
     *
     * @param $userId
     *
     * @return int
     */
    public function countUser($userId)
    {
        $number = $this->find(
            'all',
            [
                'conditions' => ['user_id' => $userId]
            ]
        )->count();
        return (int)$number;
    }

}
