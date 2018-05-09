<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppSettingTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Saito\RememberTrait;

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
    public function initialize(array $config)
    {
        $this->hasMany('Entries', ['foreignKey' => 'category_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('category_order', 'create')
            ->add(
                'category_order',
                [
                    'isNumeric' => ['rule' => 'numeric']
                ]
            )
            ->add(
                'accession',
                [
                    'isNumeric' => ['rule' => 'numeric'],
                    'range' => ['rule' => ['range', 0, 3]]
                ]
            )
            ->add(
                'accession_new_thread',
                [
                    'isNumeric' => ['rule' => 'numeric'],
                    'range' => ['rule' => ['range', 1, 3]]
                ]
            )
            ->add(
                'accession_new_posting',
                [
                    'isNumeric' => ['rule' => 'numeric'],
                    'range' => ['rule' => ['range', 1, 3]]
                ]
            );

        return $validator;
    }

    /**
     * get all categories
     *
     * @return array
     */
    public function getAllCategories()
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
}
