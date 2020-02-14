<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Controller\ErrorController;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Saito\App\Registry;
use Saito\Smiley\SmileyLoader;
use Saito\User\Userlist\UserlistModel;

class ParserComponent extends Component
{
    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        $controller = $this->getController();

        if ($controller instanceof ErrorController) {
            return;
        }

        $smilies = new SmileyLoader();
        $controller->set('smiliesData', $smilies);

        $settings = Configure::read('Saito.Settings');

        $markup = Registry::get('MarkupSettings');
        $markup->set([
                'autolink' => $settings['autolink'],
                'bbcode_img' => $settings['bbcode_img'],
                'content_embed_active' => $settings['content_embed_active'],
                'content_embed_media' => $settings['content_embed_media'],
                'content_embed_text' => $settings['content_embed_text'],
                'quote_symbol' => $settings['quote_symbol'],
                'smilies' => $settings['smilies'],
                'smiliesData' => $smilies,
                'server' => Router::fullBaseUrl(),
                'UserList' => new UserlistModel(),
                'video_domains_allowed' => $settings['video_domains_allowed'],
                'webroot' => $controller->getRequest()->getAttribute('webroot'),
        ]);
    }
}
