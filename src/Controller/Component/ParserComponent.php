<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Saito\Markup\Settings;
use Saito\User\Userlist;

class ParserComponent extends Component
{

    /**
     * @var SaitoMarkupSettings
     */
    protected $_settings;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        // is needed in Markup Behavior
        $this->_settings = new Settings(
            [
                'server' => Router::fullBaseUrl(),
                'webroot' => $this->_registry->getController()->request->webroot
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        $this->_initHelper($event->subject());
    }

    /**
     * Inits the ParserHelper for use in a View
     *
     * Call this instead of including in the controller's $helpers array.
     *
     * @param Controller $controller controller
     * @return void
     */
    protected function _initHelper(Controller $controller)
    {
        $userlist = new Userlist\UserlistModel();
        $userlist->set(TableRegistry::get('Users'));
        $smilies = new \Saito\Smiley\Cache();
        $controller->set('smiliesData', $smilies);

        $this->_settings->add(
            [
                'quote_symbol' => Configure::read(
                    'Saito.Settings.quote_symbol'
                ),
                'smiliesData' => $smilies,
                'UserList' => $userlist
            ]
        );

        $controller->helpers['Parser'] = $this->_settings->get();
    }
}
