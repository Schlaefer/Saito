<?php

namespace SaitoHelp\View\Helper;

use Cake\View\Helper;
use Commonmark\View\Helper\CommonmarkHelper;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * Helper for Saito-help
 *
 * @property CommonmarkHelper $Commonmark
 * @property HtmlHelper $Html
 * @property LayoutHelper $Layout
 * @property UrlHelper $Url
 */
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
        $text = $this->_replaceUrl($text, $CurrentUser);

        return $this->Commonmark->parse($text);
    }

    /**
     * Allow linking within the Saito app
     *
     * @param string $text text to parse with link markup
     * @param CurrentUserInterface $CurrentUser current user
     * @return string text with links replaced
     */
    private function _replaceUrl($text, $CurrentUser)
    {
        $webroot = $this->Url->build('/', true);

        $text = preg_replace_callback(
            '/\[(?P<text>.*?)\]\((?P<url>.*?)\)/',
            function ($matches) use ($CurrentUser, $webroot) {
                $text = $matches['text'];
                $url = $matches['url'];

                if (strpos($matches['url'], ':uid')) {
                    if (!$CurrentUser->isLoggedIn()) {
                        return $text;
                    }
                    $uid = $CurrentUser->getId();
                    $url = str_replace(':uid', $uid, $url);
                }

                if (strpos($url, 'webroot:') === 0) {
                    $url = str_replace('webroot:', $webroot, $url);
                }

                return "[$text]($url)";
            },
            $text
        );

        return $text;
    }
}
