<?php

namespace Admin\View\Helper;

use App\View\Helper\AppHelper;
use App\View\Helper\TimeHHelper;
use Cake\View\Helper\BreadcrumbsHelper;
use Cake\View\Helper\HtmlHelper;
use SaitoHelp\View\Helper\SaitoHelpHelper;

//@codingStandardsIgnoreStart
// @bogus the ability to see logs isn't in Saito 5 anymore
class CakeLogEntry
{

    public function __construct($text)
    {
        $lines = explode("\n", trim($text));
        $_firstLine = array_shift($lines);
        preg_match(
            '/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.*?): (.*)/',
            $_firstLine,
            $matches
        );
        $this->_time = $matches[1];
        $this->_type = $matches[2];
        $this->_message = trim($matches[3]);
        if (empty($this->_message)) {
            $this->_message = array_shift($lines);
        }
        $this->_detail = implode($lines, '<br>');
    }

    public function time()
    {
        return $this->_time;
    }

    public function type()
    {
        return $this->_type;
    }

    public function message()
    {
        return $this->_message;
    }

    public function details()
    {
        return $this->_detail;
    }
}

//@codingStandardsIgnoreStart

/**
 * @property BreadcrumbsHelper $Breadcrumbs
 * @property HtmlHelper $Html
 * @property SaitoHelpHelper $SaitoHelp
 * @property TimeHHelper $TimeH
 */
class AdminHelper extends AppHelper
{

    public $helpers = [
        'Breadcrumbs',
        'SaitoHelp',
        'Html',
        'TimeH'
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
     * cache badge
     *
     * @param string $engine engine
     * @return string
     */
    protected function _cacheBadge($engine)
    {
        switch ($engine) {
            case 'File':
                $badge = 'warning';
                break;
            case 'Apc':
            case 'Apcu':
                $badge = 'success';
                break;
            case 'Debug':
                $badge = 'important';
                break;
            default:
                $badge = 'info';
        }
        return $badge;
    }

    /**
     * badge
     *
     * @param string $text text
     * @param null $type type
     * @return mixed
     */
    public function badge($text, $type = null)
    {
        if (is_callable([$this, $type])) {
            $badge = $this->$type($text);
        } elseif (is_string(($type))) {
            $badge = $type;
        } else {
            $badge = 'info';
        }
        return $this->Html->tag(
            'span',
            $text,
            ['class' => "badge badge-$badge"]
        );
    }

    /**
     * Adds Breadcrumb item
     *
     * @see BreadcrumbsHelper::add()
     *
     */
    public function addBreadcrumb($title, $url = null, array $options = [])
    {
        $options += ['class' => ''];
        // set breadcrumb item class for Bootstrap
        $options['class'] = $options['class'] . ' breadcrumb-item';
        // last item in breadcrump is current (active) page and not linked
        if ($url === false) {
            // set breadcrumb active item class for Bootstrap
            $options['class'] .= ' active';
        }
        return $this->Breadcrumbs->add($title, $url, $options);
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
                $out .= '<button class="btn btn-mini" style="float:right;" onclick="$(\'#' . $_i . '\').toggle(); return false;">' . __(
                        'Details'
                    ) . '</button>' . "\n";
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
     */
    public function jqueryTable($selector, $sort)
    {
        $this->Html->css(
                '../js/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css',
                ['block'  => 'script']

        );
        $this->Html->script(
            [
                '../js/node_modules/datatables.net/js/jquery.dataTables.js',
                '../js/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.js'
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

    /**
     * accession to roles
     *
     * @param int $accession accession
     * @return string
     */
    public function accessionToRoles($accession)
    {
        switch ($accession) {
            case (0):
                return __('user.type.anon');
            case (1):
                return __('user.type.user');
            case (2):
                return __('user.type.mod');
            case (3):
                return __('user.type.admin');
        }

    }
}
