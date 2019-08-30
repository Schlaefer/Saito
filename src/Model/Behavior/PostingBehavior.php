<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Behavior;

use App\Lib\Model\Table\FieldFilter;
use App\Model\Entity\Entry;
use App\Model\Table\EntriesTable;
use Cake\ORM\Behavior;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Posting;
use Saito\User\CurrentUser\CurrentUserInterface;

class PostingBehavior extends Behavior
{
    /** @var CurrentUserInterface */
    private $CurrentUser;

    /** @var FieldFilter */
    private $fieldFilter;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->fieldFilter = (new fieldfilter())
            ->setConfig('create', ['category_id', 'pid', 'subject', 'text'])
            ->setConfig('update', ['category_id', 'subject', 'text']);
    }

    /**
     * Creates a new posting from user
     *
     * @param array $data raw posting data
     * @param CurrentUserInterface $CurrentUser the current user
     * @return Entry|null on success, null otherwise
     */
    public function createPosting(array $data, CurrentUserInterface $CurrentUser): ?Entry
    {
        $data = $this->fieldFilter->filterFields($data, 'create');

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
        }

        /// set user who created the posting
        $data['user_id'] = $CurrentUser->getId();
        $data['name'] = $CurrentUser->get('username');

        $this->validatorSetup($CurrentUser);

        /** @var EntriesTable */
        $table = $this->getTable();

        return $table->createEntry($data);
    }

    /**
     * Updates an existing posting
     *
     * @param Entry $posting the posting to update
     * @param array $data data the posting should be updated with
     * @param CurrentUserInterface $CurrentUser the current-user
     * @return Entry|null the posting which was asked to update
     */
    public function updatePosting(Entry $posting, array $data, CurrentUserInterface $CurrentUser): ?Entry
    {
        $data = $this->fieldFilter->filterFields($data, 'update');
        $isRoot = $posting->isRoot();
        $parent = $this->getTable()->get($posting->get('pid'));

        if (!$isRoot) {
            $data = $this->prepareChildPosting($parent, $data);
        }

        $data['edited_by'] = $CurrentUser->get('username');

        /// must be set for validation
        $data['locked'] = $posting->get('locked');
        $data['pid'] = $posting->get('pid');
        $data['time'] = $posting->get('time');
        $data['user_id'] = $posting->get('user_id');

        $this->validatorSetup($CurrentUser);
        $this->getTable()->getValidator()->add(
            'edited_by',
            'isEditingAllowed',
            ['rule' => [$this, 'validateEditingAllowed']]
        );

        /** @var EntriesTable */
        $table = $this->getTable();

        return $table->updateEntry($posting, $data);
    }

    /**
     * Populates data of an answer derived from parent the parent-posting
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

        $data['category_id'] = $parent->get('category_id');
        $data['tid'] = $parent->get('tid');

        return $data;
    }

    /**
     * Sets-up validator for the table
     *
     * @param CurrentUserInterface $CurrentUser current user
     * @return void
     */
    private function validatorSetup(CurrentUserInterface $CurrentUser): void
    {
        $this->CurrentUser = $CurrentUser;

        $this->getTable()->getValidator()->add(
            'category_id',
            'isAllowed',
            ['rule' => [$this, 'validateCategoryIsAllowed']]
        );
    }

    /**
     * check that entries are only in existing and allowed categories
     *
     * @param mixed $categoryId value
     * @param array $context context
     * @return bool
     */
    public function validateCategoryIsAllowed($categoryId, $context): bool
    {
        $isRoot = $context['data']['pid'] == 0;
        $action = $isRoot ? 'thread' : 'answer';

        // @td better return !$posting->isAnsweringForbidden();
        return $this->CurrentUser->getCategories()->permission($action, $categoryId);
    }

    /**
     * check editing allowed
     *
     * @param mixed $check value
     * @param array $context context
     * @return bool
     */
    public function validateEditingAllowed($check, $context): bool
    {
        $posting = new Posting($this->CurrentUser, $context['data']);

        return $posting->isEditingAllowed();
    }
}
