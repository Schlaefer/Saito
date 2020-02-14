<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use Api\Controller\ApiAppController;
use Cake\I18n\Time;
use Cake\View\Helper\IdGeneratorTrait;

/**
 * Class EntriesController
 *
 * @property \App\Model\Table\EntriesTable $Entries
 * @property \App\Controller\Component\PostingComponent $Posting
 */
class PreviewController extends ApiAppController
{
    use IdGeneratorTrait;

    /**
     * Generate posting preview for JSON frontend.
     *
     * @return \Cake\Http\Response|void
     */
    public function preview()
    {
        $this->loadModel('Entries');
        $this->loadComponent('Posting');

        $data = [
            'category_id' => $this->request->getData('category_id'),
            'edited_by' => null,
            'fixed' => false,
            'id' => 'preview',
            'ip' => '',
            'last_answer' => bDate(),
            'name' => $this->CurrentUser->get('username'),
            'pid' => $this->request->getData('pid') ?: 0,
            'solves' => 0,
            'subject' => $this->request->getData('subject'),
            'text' => $this->request->getData('text'),
            'user_id' => $this->CurrentUser->getId(),
            'time' => new Time(),
            'views' => 0,
        ];

        if (!empty($data['pid'])) {
            $parent = $this->Entries->get($data['pid']);
            $data = $this->Posting->prepareChildPosting($parent, $data);
        }

        /** @var \App\Model\Entity\Entry $newEntry */
        $newEntry = $this->Entries->newEntity($data);
        $errors = $newEntry->getErrors();

        if (empty($errors)) {
            // no validation errors
            $newEntry['user'] = $this->CurrentUser->getSettings();
            $newEntry['category'] = $this->Entries->Categories->find()
                ->where(['id' => $newEntry['category_id']])
                ->first();
            $posting = $newEntry->toPosting()->withCurrentUser($this->CurrentUser);
            $this->set(compact('posting'));
        } else {
            $this->set(compact('errors'));
            $this->viewBuilder()->setTemplate('/Error/json/entityValidation');
        }
    }
}
