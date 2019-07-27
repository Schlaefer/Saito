<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoHelp\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Entity;
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
        $help = $this->find($id, $lang);

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

    /**
     * Loads help file
     *
     * @param string $id [<plugin>.]<id>
     * @param string $lang folder docs/help/<langugage>
     * @return Entity|null
     */
    private function find(string $id, string $lang = 'en'): ?Entity
    {
        $findFiles = function ($id, $lang) {
            list($plugin, $id) = pluginSplit($id);
            if ($plugin) {
                $folderPath = Plugin::path($plugin);
            } else {
                $folderPath = ROOT . DS;
            }
            $folderPath .= 'docs' . DS . 'help' . DS . $lang;

            $folder = new Folder($folderPath);
            $files = $folder->find("$id(-.*?)?\.md");

            return [$files, $folderPath];
        };

        list($files, $folderPath) = $findFiles($id, $lang);

        if (empty($files)) {
            list($lang) = explode('_', $lang);
            list($files, $folderPath) = $findFiles($id, $lang);
        }

        if (!$files) {
            return null;
        }
        $name = $files[0];
        $file = new File($folderPath . DS . $name, false, 0444);
        $text = $file->read();
        $file->close();
        $data = [
            'file' => $name,
            'id' => $id,
            'lang' => $lang,
            'text' => $text
        ];
        $result = new Entity($data);

        return $result;
    }
}
