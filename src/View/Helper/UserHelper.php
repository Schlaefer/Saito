<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use App\Model\Entity\User;
use Cake\View\Helper\HtmlHelper;
use Cake\View\Helper\UrlHelper;
use Identicon\Identicon;
use Saito\RememberTrait;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\ForumsUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Class UserHelper
 *
 * @package App\View\Helper
 * @property HtmlHelper $Html
 * @property UrlHelper $Url
 */
class UserHelper extends AppHelper
{
    use RememberTrait;

    public $helpers = ['Html', 'Url'];

    /**
     * banned
     *
     * @param bool $isBanned banned
     * @return string
     */
    public function banned($isBanned)
    {
        $out = '';
        if ($isBanned) {
            $out = '<i class="fa fa-ban fa-lg"></i>';
        }

        return $out;
    }

    /**
     * generates CSS from user-preferences
     *
     * @param array $User user
     * @return string
     */
    public function generateCss(array $User)
    {
        $styles = [];

        // colors
        $cNew = $User['user_color_new_postings'];
        $cOld = $User['user_color_old_postings'];
        $cAct = $User['user_color_actual_posting'];

        $aMetatags = ['', ':link', ':visited', ':hover', ':active'];
        foreach ($aMetatags as $aMetatag) {
            if (!empty($cOld) && $cOld !== '#') {
                $styles[] = ".et-root .et$aMetatag, .et-reply .et$aMetatag	{ color: $cOld; }";
            }
            if (!empty($cNew) && $cNew !== '#') {
                $styles[] = ".et-new .et$aMetatag { color: $cNew; }";
            }
            if (!empty($cAct) && $cAct !== '#') {
                $styles[] = ".et-current .et$aMetatag { color: $cAct; }";
            }
        }

        return '<style type="text/css">' . implode(" ", $styles) . '</style>';
    }

    /**
     * Creates link to user's external (non-Saito) homepage
     *
     * @param string $url user provided URL-string
     * @return string link or escaped string
     */
    public function linkExternalHomepage(string $url): string
    {
        $link = $url;

        if (substr($link, 0, 4) == 'www.') {
            $link = 'http://' . $link;
        }
        if (substr($link, 0, 4) == 'http') {
            $text = '<i class="fa fa-home fa-lg"></i>';

            return $this->Html->link($text, $link, ['escape' => false]);
        }

        return h($url);
    }

    /**
     * Link to user-profile
     *
     * @param User|ForumsUserInterface $user user
     * @param bool|CurrentUserInterface $link link
     * @param array $options options
     * @return string
     */
    public function linkToUserProfile($user, $link = true, array $options = []): string
    {
        $options += [
            'title' => $user->get('username'),
            'escape' => true,
        ];
        $id = $user->get('id');

        $name = $options['title'];
        unset($options['title']);

        if (empty($id)) {
            // removed user
            $html = $name;
        } elseif (
            ($link === true)
            || ($link instanceof CurrentUserInterface && $link->isLoggedIn())
        ) {
            return $this->Html->link($name, '/users/view/' . $id, $options);
        } else {
            $html = $name;
        }
        if ($options['escape']) {
            $html = h($html);
        }

        return $html;
    }

    /**
     * Get image avatar for user
     *
     * @param User|ForumsUserInterface $user User
     * @param array $options options
     * @return string HTML
     */
    public function getAvatar($user, array $options = [])
    {
        $getAvatar = function () use ($user, $options) {
            Stopwatch::start('UserHelper::getAvatar()');
            $defaults = [
                'class' => 'avatar-image',
                'link' => [
                    'class' => 'avatar-link',
                    'escape' => false,
                ],
                'size' => 50,
                'style' => '',
                'tag' => 'span',
            ];
            $options = array_replace_recursive($defaults, $options);
            $size = $options['size'];

            $avatar = $user->get('avatar');
            if ($avatar) {
                $userId = $user->get('id');
                $url = "useruploads/users/avatar/{$userId}/square_{$avatar}";
                $imgUri = $this->Url->assetUrl($url);
            } else {
                $name = $user->get('username');
                $hdpi = 2 * $size;
                $imgUri = (new Identicon())->getImageDataUri($name, $hdpi);
            }

            $style = "background-image: url({$imgUri});" . $options['style'];

            $html = $this->Html->tag(
                $options['tag'],
                '',
                [
                    'class' => $options['class'],
                    'style' => $style,
                ]
            );

            if ($options['link'] !== false) {
                $options['link']['title'] = $html;
                $html = $this->linkToUserProfile($user, true, $options['link']);
            }
            Stopwatch::end('UserHelper::getAvatar()');

            return $html;
        };

        $name = $user->get('username');
        $hash = 'avatar.' . md5($name . serialize($options));

        return $this->remember($hash, $getAvatar);
    }
}
