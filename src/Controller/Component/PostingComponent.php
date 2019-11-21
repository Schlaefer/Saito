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

use App\Model\Entity\Entry;
use App\Model\Table\EntriesTable;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Saito\Exception\SaitoForbiddenException;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Posting;
use Saito\User\CurrentUser\CurrentUserInterface;

class PostingComponent extends Component
{

    /**
     * Creates a new posting from user
     *
     * @param array $data raw posting data
     * @param CurrentUserInterface $CurrentUser the current user
     * @return Entry|null on success, null otherwise
     */
    public function create(array $data, CurrentUserInterface $CurrentUser): ?Entry
    {
        if (!empty($data['pid'])) {
            /// new posting is answer to existing posting
            $parent = $this->getTable()->get($data['pid']);

            if (empty($parent)) {
                throw new \InvalidArgumentException(
                    'Parent posting for creating a new answer not found.',
                    1564756571
                );
            }

            $data = $this->prepareChildPosting($parent, $data);
        } else {
            /// if no pid is provided the new posting is root-posting
            $data['pid'] = 0;
            // We can't wait for entity-validation cause we check the category
            // in permissions.
            if (!isset($data['category_id'])) {
                throw new \InvalidArgumentException(
                    'No category for new posting provided.',
                    1573123345
                );
            }
        }

        $posting = new Posting($data + ['id' => 0]);
        $action = $posting->isRoot() ? 'thread' : 'answer';
        // @td better return !$posting->isAnsweringForbidden();
        $allowed = $CurrentUser->getCategories()->permission($action, $posting->get('category_id'));
        if ($allowed !== true) {
            throw new SaitoForbiddenException('Creating new posting not allowed.');
        }

        return $this->getTable()->createEntry($data);
    }

    /**
     * Updates an existing posting
     *
     * @param Entry $entry the posting to update
     * @param array $data data the posting should be updated with
     * @param CurrentUserInterface $CurrentUser the current-user
     * @return Entry|null the posting which was asked to update
     */
    public function update(Entry $entry, array $data, CurrentUserInterface $CurrentUser): ?Entry
    {
        $isRoot = $entry->isRoot();

        if (!$isRoot) {
            $parent = $this->getTable()->get($entry->get('pid'));
            $data = $this->prepareChildPosting($parent, $data);
        }

        $allowed = $entry->toPosting()->withCurrentUser($CurrentUser)->isEditingAllowed();
        if ($allowed !== true) {
            throw new SaitoForbiddenException('Updating posting not allowed.');
        }

        return $this->getTable()->updateEntry($entry, $data);
    }

    /**
     * Populates data of an child derived from its parent-posting
     *
     * @param BasicPostingInterface $parent parent data
     * @param array $data current posting data
     * @return array populated $data
     */
    public function prepareChildPosting(BasicPostingInterface $parent, array $data): array
    {
        if (empty($data['subject'])) {
            // if new subject is empty use the parent's subject
            $data['subject'] = $parent->get('subject');
        }

        $data['category_id'] = $data['category_id'] ?? $parent->get('category_id');
        $data['tid'] = $parent->get('tid');

        return $data;
    }

    /**
     * Get Entries table
     *
     * @return EntriesTable
     */
    protected function getTable(): EntriesTable
    {
        /** @var EntriesTable */
        $table = TableRegistry::getTableLocator()->get('Entries');

        return $table;
    }
}
