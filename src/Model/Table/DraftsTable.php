<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppTable;
use App\Model\Entity\Entry;
use Cake\Chronos\Chronos;
use Cake\Event\Event;
use Cake\ORM\Rule\IsUnique;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Table storing the drafts for unfinished posting submissions.
 *
 * Indices:
 * - user_id, pid - Main lookup index for retrieving and checking for uniqness.
 * Have user_id first to use it for other purposes (find drafts for user).
 * - modified - Used for garbage collection deleting outdated drafts.
 */
class DraftsTable extends AppTable
{
    /** @var string Creation time after a draft is considered outdated. */
    public const OUTDATED = '-30 days';

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->addBehavior(
            'Cron.Cron',
            ['outdatedGc' => ['id' => 'Drafts.outdatedGc', 'due' => '+1 day']]
        );
        $this->addBehavior('Timestamp');
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator): Validator
    {
        /// pid
        $validator
            ->requirePresence('pid', 'create');

        /// subject
        $validator
            ->allowEmptyString('subject')
            ->add(
                'subject',
                [
                    'maxLength' => [
                        'rule' => ['maxLength', $this->getConfig('subject_maxlength')],
                    ],
                ]
            );

        /// text
        $validator->allowEmptyString('text');

        /// user_id
        $validator
            ->requirePresence('user_id', 'create');

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        /// Trim whitespace on subject and text
        $toTrim = ['subject', 'text'];
        foreach ($toTrim as $field) {
            if (!empty($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules = parent::buildRules($rules);

        $rules->addCreate(new IsUnique(['pid', 'user_id']));

        $rules->add(
            function ($entity) {
                $validated = false;
                $fields = ['subject', 'text'];
                foreach ($fields as $field) {
                    if (!empty($entity->get($field))) {
                        $validated = true;
                        break;
                    }
                }

                return $validated;
            },
            'checkThatAtLeastOnFieldIsPopulated',
            ['errorField' => 'oneNotEmpty', 'message' => 'dummy']
        );

        return $rules;
    }

    /**
     * Deletes a draft which might have been the source for a posting.
     *
     * @param Entry $entry The posting which might have been created by a draft.
     * @return void
     */
    public function deleteDraftForPosting(Entry $entry): void
    {
        $where = ['pid' => $entry->get('pid'), 'user_id' => $entry->get('user_id')];
        $this->deleteAll($where);
    }

    /**
     * Garbage collect outdated drafts.
     *
     * @return void
     */
    public function outdatedGc(): void
    {
        $this->deleteAll(['modified <' => new Chronos(self::OUTDATED)]);
    }
}
