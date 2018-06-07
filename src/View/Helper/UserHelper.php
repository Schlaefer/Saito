<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use App\Model\Entity\User;
use Cake\ORM\Entity;
use Identicon\Identicon;
use Saito\RememberTrait;
use Saito\User\ForumsUserInterface;
use Saito\User\SaitoUser;
use Stopwatch\Lib\Stopwatch;

/**
 * Class UserHelper
 *
 * @package App\View\Helper
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
     * Translates user types
     *
     * @param string $type type
     * @return mixed
     */
    public function type($type)
    {
        // write out all __() strings for l10n
        switch ($type) {
            case 'user':
                return __('user.type.user');
            case 'mod':
                return __('user.type.mod');
            case 'admin':
                return __('user.type.admin');
        }
    }

    /**
     * Creates link to user contact page with image
     *
     * @param Entity $user user
     * @return string
     */
    public function contact(Entity $user)
    {
        $out = '';
        if ($user->get('personal_messages') && $user->get('user_email')) {
            $out = $this->Html->link(
                '<i class="fa fa-envelope-o fa-lg"></i>',
                ['controller' => 'contacts', 'action' => 'user', $user['id']],
                ['escape' => false]
            );
        }

        return $out;
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
     * @param bool $link link
     * @param array $options options
     * @return string
     */
    public function linkToUserProfile($user, $link = true, array $options = [])
    {
        $options += [
            'title' => $user->get('username'),
            'escape' => true
        ];
        $id = $user->get('id');

        $name = $options['title'];
        unset($options['title']);

        if (empty($id)) {
            // removed user
            $html = $name;
        } elseif ($link || ($link instanceof ForumsUserInterface && $link->isLoggedIn())
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
                    'escape' => false
                ],
                'size' => 50,
                'style' => '',
                'tag' => 'span'
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
                $imgUri = (new Identicon)->getImageDataUri($name, $hdpi);
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
