<?php

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

    protected $_SaitoUser = null;

    public $helpers = ['Html', 'Url'];

    /**
     * {@inheritDoc}
     */
    public function beforeRender()
    {
        $this->_SaitoUser = new SaitoUser();
    }

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
        $_styles = [];

        // colors
        $_cNew = $User['user_color_new_postings'];
        $_cOld = $User['user_color_old_postings'];
        $_cAct = $User['user_color_actual_posting'];

        $_aMetatags = ['', ':link', ':visited', ':hover', ':active'];
        foreach ($_aMetatags as $_aMetatag) {
            if (!empty($_cOld) && $_cOld !== '#') {
                $_styles[] = ".et-root .et$_aMetatag, .et-reply .et$_aMetatag	{ color: $_cOld; }";
            }
            if (!empty($_cNew) && $_cNew !== '#') {
                $_styles[] = ".et-new .et$_aMetatag { color: $_cNew; }";
            }
            if (!empty($_cAct) && $_cAct !== '#') {
                $_styles[] = ".et-current .et$_aMetatag { color: $_cAct; }";
            }
        }

        return '<style type="text/css">' . implode(" ", $_styles) . '</style>';
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
     * Creates Homepage Links with Image from Url
     *
     * @param string $url url
     * @return string
     */
    public function homepage($url)
    {
        $out = $url;
        if (is_string($url)) {
            if (substr($url, 0, 4) == 'www.') {
                $url = 'http://' . $url;
            }
            if (substr($url, 0, 4) == 'http') {
                $out = $this->Html->link(
                    '<i class="fa fa-home fa-lg"></i>',
                    $url,
                    ['escape' => false]
                );
            } else {
                $out = h($url);
            }
        }
        return $out;
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
                $imgUri = (new Identicon)->getImageDataUri($name, $size);
            }

            $style = "background-image: url({$imgUri});" . $options['style'];

            $html = $this->Html->tag(
                $options['tag'],
                '',
                [
                    'class' => $options['class'],
                    'style' => "width: {$size}px; height: {$size}px; {$style}"
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
