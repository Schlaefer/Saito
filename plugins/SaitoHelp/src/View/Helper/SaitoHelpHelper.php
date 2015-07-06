<?php

namespace SaitoHelp\View\Helper;

use Cake\View\Helper;
use Saito\User\CurrentUser\CurrentUserInterface;

class SaitoHelpHelper extends Helper
{
    public $helpers = ['Commonmark.Commonmark', 'Html', 'Layout', 'Url'];

    /**
     * Create a help icon linking to a help page
     *
     * @param string $id help page id
     * @param array $options options
     * @return mixed
     */
    public function icon($id, array $options = [])
    {
        $options += ['label' => '', 'target' => '_blank'];
        $options = ['class' => 'shp-icon', 'escape' => false] + $options;

        if ($options['label'] === true) {
            $options['label'] = __('Help');
        }
        if (!empty($options['label'])) {
            $options['label'] = h($options['label']);
        }

        $title = $this->Layout->textWithIcon(
            $options['label'],
            'question-circle'
        );
        unset($options['label']);

        return $this->Html->link($title, "/help/$id", $options);
    }

    /**
     * Parse text
     *
     * @param string $text text to parse
     * @param CurrentUserInterface $CurrentUser current user
     * @return string
     */
    public function parse($text, CurrentUserInterface $CurrentUser)
    {
        $this->_CurrentUser = $CurrentUser;
        $this->_webroot = $this->Url->build('/', true);

        $text = preg_replace_callback(
            '/\[(?P<text>.*?)\]\((?P<url>.*?)\)/',
            [$this, '_replaceUrl'],
            $text
        );
        return $this->Commonmark->parse($text);
    }

    /**
     * Allow linking within the Saito app
     *
     * @param array $matches matches
     * @return string
     */
    protected function _replaceUrl(array $matches)
    {
        $text = $matches['text'];
        $url = $matches['url'];

        if (strpos($matches['url'], ':uid')) {
            if (!$this->_CurrentUser->isLoggedIn()) {
                return $text;
            }
            $uid = $this->_CurrentUser->getId();
            $url = str_replace(':uid', $uid, $url);
        }

        if (strpos($url, 'webroot:') === 0) {
            $url = str_replace('webroot:', $this->_webroot, $url);
        }

        return "[$text]($url)";
    }
}
