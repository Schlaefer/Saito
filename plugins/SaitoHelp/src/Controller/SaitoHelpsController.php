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
use Cake\Http\Response;
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
     * @return Response|Null
     */
    public function view($lang, $id)
    {
        $help = $this->find($lang, $id);

        if (!$help && $lang !== 'en') {
            // Help file at least for localization not found. Try to fallback to
            // english default language.
            return $this->redirect("/help/en/$id");
        }
        if ($help) {
            $this->set('help', $help);
        } else {
            $this->Flash->set(__('sh.nf'), ['element' => 'error']);

            return $this->redirect('/');
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
        $this->Authentication->allowUnauthenticated(['languageRedirect', 'view']);
    }

    /**
     * Loads help file
     *
     * @param string $lang Language. Folder docs/help/<langugage>
     * @param string $id Plugin file id. [<plugin>.]<id>
     * @return Entity|null Null if help file wan't found
     */
    private function find(string $lang, string $id): ?Entity
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
            'text' => $text,
        ];
        $result = new Entity($data);

        return $result;
    }
}
