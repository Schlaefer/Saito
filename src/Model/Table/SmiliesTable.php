<?php

namespace App\Model\Table;

use App\Lib\Model\Table\AppSettingTable;
use Cake\Core\Configure;
use \Stopwatch\Lib\Stopwatch;

class SmiliesTable extends AppSettingTable
{

    public $name = 'Smiley';

    // @todo 3.0
    public $validate = [
        'order' => [
            'numeric' => [
                'rule' => ['numeric']
            ]
        ]
    ];

    protected $_smilies;

    public function initialize(array $config)
    {
        $this->hasMany('SmileyCodes', ['foreignKey' => 'smiley_id']);
    }

    public function load($force = false)
    {
        if ($force) {
            $this->_smilies = null;
            $this->clearCache();
        }

        if ($this->_smilies !== null) {
            return $this->_smilies;
        }

        $this->_smilies = [];
        $smiliesRaw = $this->find(
            'all',
            [
                'contain' => ['SmileyCodes'],
                'order' => ['sort' => 'ASC']
            ]
        )
            ->hydrate(false);

        foreach ($smiliesRaw as $smileyRaw) {
            // 'image' defaults to 'icon'
            if (empty($smileyRaw['image'])) {
                $smileyRaw['image'] = $smileyRaw['icon'];
            }
            // @bogus: if title is unknown it should be a problem
            $title = $smileyRaw['title'];
            if ($title === null) {
                $smileyRaw['title'] = '';
            }
            // set type
            $smileyRaw['type'] = $this->_getType($smileyRaw);

            //= adds smiley-data to every smiley-code
            if (isset($smileyRaw['smiley_codes'])) {
                $codes = $smileyRaw['smiley_codes'];
                unset($smileyRaw['id'], $smileyRaw['smiley_codes']);
                foreach ($codes as $code) {
                    $smileyRaw['code'] = $code['code'];
                    $this->_smilies[] = $smileyRaw;
                }
            }
        }

        Stopwatch::stop('Smiley::load');
        return $this->_smilies;
    }

    /**
     * detects smiley type
     *
     * @param array $smiley
     * @return string image|font
     */
    protected function _getType($smiley)
    {
        if (preg_match('/^.*\.[\w]{3,4}$/i', $smiley['image'])) {
            return 'image';
        } else {
            return 'font';
        }
    }

}
