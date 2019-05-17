<?php

namespace SaitoHelp\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use SaitoHelp\Model\Table\SaitoHelpTable;

/**
 * @property SaitoHelpTable $SaitoHelp
 */
class SaitoHelpsController extends AppController
{
    /**
     * redirects help/<id> to help/<current language>/id
     *
     * @param string $id help page ID
     * @return void
     */
    public function languageRedirect($id)
    {
        $this->autoRender = false;
        $language = Configure::read('Saito.language');
        $this->redirect("/help/$language/$id");
    }

    /**
     * View a help page.
     *
     * @param string $lang language
     * @param string $id help page ID
     * @return void
     */
    public function view($lang, $id)
    {
        $this->loadModel('SaitoHelp.SaitoHelp');
        $help = $this->SaitoHelp->find(
            'first',
            [
                'conditions' => [
                    'id' => $id,
                    'language' => $lang
                ]
            ]
        );
        // try fallback to english default language
        if (!$help && $lang !== 'en') {
            $this->redirect("/help/en/$id");
        }
        if ($help) {
            $this->set('help', $help);
        } else {
            $this->Flash->set(__('sh.nf'), ['element' => 'error']);
            $this->redirect('/');

            return;
        }

        $isCore = !strpos($id, '.');
        $this->set(compact('isCore'));

        $this->set('titleForPage', __('Help'));
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow();
    }
}
