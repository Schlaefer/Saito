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

use Admin\Lib\CakeLogEntry;
use App\View\Helper\AppHelper;
use Cake\Cache\Cache;

/**
 * @property \Cake\View\Helper\BreadcrumbsHelper $Breadcrumbs
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \SaitoHelp\View\Helper\SaitoHelpHelper $SaitoHelp
 * @property \App\View\Helper\TimeHHelper $TimeH
 */
class AdminHelper extends AppHelper
{
    public $helpers = [
        'Breadcrumbs',
        'SaitoHelp',
        'Html',
        'TimeH',
    ];

    /**
     * help
     *
     * @param string $id id
     * @return mixed
     */
    public function help($id)
    {
        return $this->SaitoHelp->icon($id, ['style' => 'float: right;']);
    }

    /**
     * Get badge type for an engine
     *
     * @param string $engine engine-Id
     * @return string
     */
    public function badgeForCache(string $engine): string
    {
        $class = get_class(Cache::engine($engine));
        $class = explode('\\', $class);
        $class = str_replace('Engine', '', end($class));

        switch ($class) {
            case 'File':
                $type = 'warning';
                break;
            case 'Apc':
            case 'Apcu':
                $type = 'success';
                break;
            case 'Debug':
                $type = 'important';
                break;
            default:
                $type = 'info';
        }

        return $this->badge($class, $type);
    }

    /**
     * badge
     *
     * @param string $text text
     * @param string $badge type
     * @return string
     */
    public function badge(string $text, string $badge = 'info'): string
    {
        return $this->Html->tag(
            'span',
            $text,
            ['class' => "badge badge-$badge"]
        );
    }

    /**
     * format cake log
     *
     * @bogus the ability to see logs isn't in Saito 5 anymore
     *
     * @param string $log log
     * @return string
     */
    public function formatCakeLog($log)
    {
        $_nErrorsToShow = 20;
        $errors = preg_split(
            '/(?=^\d{4}-\d{2}-\d{2})/m',
            $log,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        if (empty($errors)) {
            return '<p>' . __('No log file found.') . '</p>';
        }

        $out = '';
        $k = 0;
        $errors = array_reverse($errors);
        foreach ($errors as $error) {
            $e = new CakeLogEntry($error);
            $_i = self::tagId();
            $_details = $e->details();
            if (!empty($_details)) {
                $out .= '<button class="btn btn-mini" style="float:right;" onclick="$(\'#'
                    . $_i
                    . '\').toggle(); return false;">'
                    . __('Details')
                    . '</button>'
                    . "\n";
            }
            $out .= '<pre style="font-size: 10px;">' . "\n";
            $out .= '<div class="row"><div class="span2" style="text-align: right">';
            $out .= $this->TimeH->formatTime($e->time(), 'eng');

            $out .= '</div>';
            $out .= '<div class="span7">';
            $out .= $e->message();
            if (!empty($_details)) {
                $out .= '<span id="' . $_i . '" style="display: none;">' . "\n";
                $out .= $_details;
                $out .= '</span>';
            }
            $out .= '</div></div>';
            $out .= '</pre>' . "\n";
            if ($k++ > $_nErrorsToShow) {
                break;
            }
        }

        return $out;
    }

    /**
     * jquery table
     *
     * @param string $selector selector
     * @param string $sort sort
     *
     * @return void
     */
    public function jqueryTable($selector, $sort)
    {
        $this->Html->css(
            '../js/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css',
            ['block' => 'script']
        );
        $this->Html->script(
            [
                '../js/node_modules/datatables.net/js/jquery.dataTables.js',
                '../js/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.js',
            ],
            ['block' => 'script']
        );

        $script = <<<EOF
$(function() {
    var userTable = $('{$selector}').DataTable();
});
EOF;

        $this->Html->scriptBlock($script, ['block' => 'script']);
    }
}
