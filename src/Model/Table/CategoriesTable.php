<?php

	namespace App\Model\Table;

	use App\Lib\Model\Table\AppSettingTable;
	use Cake\Cache\Cache;
    use Cake\Datasource\Exception\RecordNotFoundException;
    use Cake\Event\Event;
	use Cake\ORM\Entity;
    use Cake\ORM\TableRegistry;
    use Cake\Validation\Validator;
    use Saito\RememberTrait;

    class CategoriesTable extends AppSettingTable {

        use RememberTrait;

        // @todo 3.0 remove
		public $name = 'Category';

        // @todo 3.0
		public $cacheQueries = true;

		public function initialize(array $config) {
			$this->hasMany('Entries', ['foreignKey' => 'cateogry_id']);
		}

        public function validationDefault(Validator $validator)
        {
            $validator
                ->allowEmpty('category_order', 'create')
                ->add('category_order', [
                    'isNumeric' => ['rule' => 'numeric']
                ])
                ->add('accession', [
                    'isNumeric' => ['rule' => 'numeric'],
                    'range' => ['rule' => ['range', 0, 3]]
                ])
                ->add('accession_new_thread', [
                    'isNumeric' => ['rule' => 'numeric'],
                    'range' => ['rule' => ['range', 1, 3]]
                ])
                ->add('accession_new_posting', [
                    'isNumeric' => ['rule' => 'numeric'],
                    'range' => ['rule' => ['range', 1, 3]]
                ]);
            return $validator;
        }

        public function getAllCategories() {
            $key = 'Saito.Cache.Categories';
            return $this->rememberStatic($key,
                function () use ($key) {
                    return $this->find('all', ['valueField' => 'category'])
                        ->cache($key)
                        ->order(['category_order' => 'ASC'])
                        ->all();
                });
        }

        /**
         * Merge categories
         *
         * Move all postings and then delete the source category
         *
         * @param $sourceId
         * @param $targetId
         * @throws RecordNotFoundException
         */
		public function merge($sourceId, $targetId) {
            $source = $this->get($sourceId);
            $target = $this->get($targetId);


			if ($source->get('id') === $target->get('id')) {
                throw new \RuntimeException("Can't merge category onto itself.",
                    1434009121);
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
         * @param $categoryId
         * @throws RecordNotFoundException
         */
		public function deleteWithAllEntries($categoryId) {
            $category = $this->get($categoryId);
            $Entries = TableRegistry::get('Entries');
            $Entries->deleteAll(
                ['category_id' => $category->get('id')]
			);
			$this->delete($category);
		}

		/**
		 * Updates thread counter from postings table
		 *
		 * @return integer current thread count
         * // @todo 3.0 is this working?
		 */
		public function updateThreadCounter($categoryId) {
			// @performance
			$count = $this->Entries->find()
				->where(['pid' => 0, 'category_id' => $categoryId])
				->count();
			// updateAll doesn't trigger afterSave and doesn't empty the cache on a
			// thread counter update.
			$this->updateAll(['thread_count' => $count], ['id' => $categoryId]);
			return $count;
		}

	}
