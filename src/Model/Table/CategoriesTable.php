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

use App\Lib\Model\Table\AppSettingTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Saito\App\Registry;
use Saito\RememberTrait;
use Saito\User\Permission\Permissions;

/**
 * Class CategoriesTable
 *
 * @package App\Model\Table
 */
class CategoriesTable extends AppSettingTable
{
    use RememberTrait;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        $this->hasMany('Entries', ['foreignKey' => 'category_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->allowEmptyString('category_order', 'create')
            ->add(
                'category_order',
                [
                    'isNumeric' => ['rule' => 'numeric'],
                ]
            )
            ->add(
                'accession',
                'validateRoleExists',
                [
                    'rule' => [$this, 'validateRoleExists'],
                ]
            )
            ->add(
                'accession_new_thread',
                'validateRoleExists',
                [
                    'rule' => [$this, 'validateRoleExists'],
                ]
            )
            ->add(
                'accession_new_posting',
                'validateRoleExists',
                [
                    'rule' => [$this, 'validateRoleExists'],
                ]
            );

        return $validator;
    }

    /**
     * Get all categories in sort order
     *
     * @return ResultSetInterface
     */
    public function getAllCategories(): ResultSetInterface
    {
        $key = 'Saito.Cache.Categories';

        return $this->rememberStatic(
            $key,
            function () use ($key) {
                return $this->find('all')
                    ->cache($key)
                    ->order(['category_order' => 'ASC'])
                    ->all();
            }
        );
    }

    /**
     * Merge categories
     *
     * Move all postings and then delete the source category
     *
     * @param int $sourceId id
     * @param int $targetId id
     * @return void
     * @throws RecordNotFoundException
     */
    public function merge($sourceId, $targetId)
    {
        $source = $this->get($sourceId);
        $target = $this->get($targetId);

        if ($source->get('id') === $target->get('id')) {
            throw new \RuntimeException(
                "Can't merge category onto itself.",
                1434009121
            );
        }

        $Entries = TableRegistry::get('Entries');
        $Entries->updateAll(
            ['category_id' => $target->get('id')],
            ['category_id' => $source->get('id')]
        );
        $this->deleteWithAllEntries($source->get('id'));
    }

    /**
     * Delete a category and all postings in it
     *
     * @param int $categoryId id
     * @return void
     * @throws RecordNotFoundException
     */
    public function deleteWithAllEntries($categoryId)
    {
        $category = $this->get($categoryId);
        $Entries = TableRegistry::get('Entries');
        $Entries->deleteAll(
            ['category_id' => $category->get('id')]
        );
        $this->delete($category);
    }

    /**
     * Validate that a role for the category actually exists
     *
     * @param string $roleId The role-ID int
     * @return bool
     */
    public function validateRoleExists($roleId): bool
    {
        /** @var Permissions */
        $permissions = Registry::get('Permissions');
        $roles = $permissions->getRoles()->getAvailable(true);
        $roleIds = array_column($roles, 'id');

        return in_array((int)$roleId, $roleIds);
    }
}
