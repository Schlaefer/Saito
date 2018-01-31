<?php

namespace Saito\Smiley;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SmileyLoader
{

    protected $_smilies;

    /**
     * Get all smilies.
     *
     * @return array|mixed
     */
    public function get()
    {
        if ($this->_smilies !== null) {
            return $this->_smilies;
        }
        $this->_smilies = Cache::remember('Saito.Smilies.data', function () {
            $Smilies = TableRegistry::get('Smilies');
            $smiliesRaw = $Smilies->find()
                ->contain(['SmileyCodes'])
                ->order(['sort' => 'ASC'])
                ->hydrate(false)
                ->all()
                ->toArray();

            $smilies = [];
            foreach ($smiliesRaw as $smiley) {
                // 'image' defaults to 'icon'
                if (empty($smiley['image'])) {
                    $smiley['image'] = $smiley['icon'];
                }
                // @bogus: if title is unknown it should be a problem
                $title = $smiley['title'];
                if ($title === null) {
                    $smiley['title'] = '';
                }
                // set type
                $smiley['type'] = $this->_getType($smiley);

                //= adds smiley-data to every smiley-code
                if (isset($smiley['smiley_codes'])) {
                    $codes = $smiley['smiley_codes'];
                    unset($smiley['id'], $smiley['smiley_codes']);
                    foreach ($codes as $code) {
                        $smiley['code'] = $code['code'];
                        $smilies[] = $smiley;
                    }
                }
            }

            return $smilies;
        });

        return $this->_smilies;
    }

    /**
     * detects smiley type
     *
     * @param array $smiley smiley
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

    /**
     * Get additional smilies
     *
     * @return mixed
     */
    public function getAdditionalSmilies()
    {
        return Configure::read('Saito.markItUp.additionalButtons');
    }
}
