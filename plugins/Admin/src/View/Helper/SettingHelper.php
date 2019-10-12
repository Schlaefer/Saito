<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Admin\View\Helper;

use App\View\Helper\AppHelper;
use Cake\View\Helper\HtmlHelper;
use SaitoHelp\View\Helper\SaitoHelpHelper;

/**
 * Setting Helper
 *
 * @property HtmlHelper $Html
 * @property SaitoHelpHelper $SaitoHelp
 */
class SettingHelper extends AppHelper
{

    protected $_headers = [];

    public $helpers = [
        'Html',
        'SaitoHelp'
    ];

    /**
     * table
     *
     * @param string $tableName name
     * @param array $settingNames setting names
     * @param array $Settings settings
     * @param array $options options
     * @return mixed|string
     */
    public function table(
        $tableName,
        array $settingNames,
        $Settings,
        array $options = []
    ) {
        $defaults = [
            'nav-title' => $tableName
        ];
        $options += $defaults;

        $out = $this->tableHeaders();
        $anchors = '';
        foreach ($settingNames as $name) {
            $out .= $this->tableRow($name, $Settings);
            $anchors .= '<a name="' . $name . '"></a>';
        }
        $key = $this->addHeader($options['nav-title']);
        $out = '<table class="table table-striped table-bordered table-condensed">' .
            $out . '</table>';

        $sh = '';
        if (!empty($options['sh'])) {
            $sh = $this->SaitoHelp->icon(
                $options['sh'],
                ['style' => 'float: right; margin: 1em']
            );
        }

        $out = '<div id="navHeaderAnchor' . $key . '"></div>' .
            $sh .
            $anchors .
            '<h2 >' . $tableName . '</h2>' .
            $out;

        return $out;
    }

    /**
     * add header
     *
     * @param string $header header
     * @return int
     */
    public function addHeader($header)
    {
        $id = count($this->_headers) + 1;
        $this->_headers[$id] = $header;

        return $id;
    }

    /**
     * get headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * table row
     *
     * @param string $name name
     * @param array $Settings settings
     * @return mixed
     */
    public function tableRow($name, $Settings)
    {
        return $this->Html->tableCells(
            [
                __d('nondynamic', $name),
                $Settings[$name],
                "<p>" . __d('nondynamic', $name . '_exp') . "</p>",
                $this->Html->link(
                    __('edit'),
                    ['controller' => 'settings', 'action' => 'edit', $name],
                    ['class' => 'btn btn-primary']
                )
            ]
        );
    }

    /**
     * Tableheaders
     *
     * @return mixed
     */
    public function tableHeaders()
    {
        return $this->Html->tableHeaders(
            [
                __('Key'),
                __('Value'),
                __('Explanation'),
                __('Actions')
            ]
        );
    }
}
