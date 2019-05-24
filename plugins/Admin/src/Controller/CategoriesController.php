<?php

namespace Admin\Controller;

use App\Model\Table\CategoriesTable;
use Cake\Http\Exception\BadRequestException;

/**
 * @property CategoriesTable $Categories
 */
class CategoriesController extends AdminAppController
{

    public $paginate = [
        'order' => [
            'Categories.category_order' => 'asc'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Categories');
    }

    /**
     * show all categories
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'limit' => 1000, // limit high enough so that no paging should occur
            'order' => ['Categories.category_order' => 'ASC']
        ];
        $this->set('categories', $this->paginate());
    }

    /**
     * add new category
     *
     * @return \Cake\Network\Response|void
     */
    public function add()
    {
        $category = $this->Categories->newEntity();
        if ($this->request->is('post')) {
            $category = $this->Categories->patchEntity(
                $category,
                $this->request->getData()
            );
            if ($this->Categories->save($category)) {
                $this->Flash->set(
                    __('cat.save.success'),
                    ['element' => 'success']
                );

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->set(
                    __('The category could not be saved. Please, try again.'),
                    ['element' => 'error']
                );
            }
        }
        $this->set(compact('category'));
    }

    /**
     * edit category
     *
     * @param string $id category-ID
     * @return \Cake\Network\Response|void
     */
    public function edit($id)
    {
        try {
            if (empty($id)) {
                throw new BadRequestException;
            }
            $category = $this->Categories->get($id);
        } catch (\Exception $e) {
            $this->Flash->set(__('Invalid category'), ['element' => 'error']);

            return $this->redirect(['action' => 'index']);
        }
        if ($this->request->is(['post', 'put'])) {
            $category = $this->Categories->patchEntity(
                $category,
                $this->request->getData()
            );
            if ($this->Categories->save($category)) {
                $this->Flash->set(
                    __('cat.save.success'),
                    ['element' => 'success']
                );
                $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->set(
                    __('The category could not be saved. Please, try again.'),
                    ['element' => 'error']
                );
            }
        }
        $this->set(compact('category'));
    }

    /**
     * delete category
     *
     * @param string $id category-ID
     *
     * @return \Cake\Network\Response|void
     */
    public function delete($id)
    {
        try {
            if (empty($id)) {
                throw new BadRequestException;
            }
            $category = $this->Categories->get($id);
        } catch (\Exception $e) {
            $this->Flash->set(__('Invalid category'), ['element' => 'error']);

            return $this->redirect(['action' => 'index']);
        }

        switch ($this->request->getData('mode')) {
            case ('move'):
                $targetId = (int)$this->request->getData('targetCategory');
                try {
                    $this->Categories->merge($category->get('id'), $targetId);
                    $this->Flash->set(
                        __('Category moved.'),
                        ['element' => 'success']
                    );

                    return $this->redirect(['action' => 'index']);
                } catch (\Exception $e) {
                    $this->Flash->set(
                        __('Error moving category.'),
                        ['element' => 'error']
                    );

                    return $this->redirect($this->referer());
                }
                break;
            case ('delete'):
                try {
                    $this->Categories->deleteWithAllEntries(
                        $category->get('id')
                    );
                    $this->Flash->set(
                        __('Category deleted.'),
                        ['element' => 'success']
                    );

                    return $this->redirect(['action' => 'index']);
                } catch (\Exception $e) {
                    $this->Flash->set(
                        __('Error deleting category.'),
                        ['element' => 'error']
                    );
                }
        }

        /* get categories for target <select> */
        $targetCategories = $this->CurrentUser->Categories->getAll(
            'read',
            'list'
        );
        unset($targetCategories[$id]);

        $this->set(compact('targetCategories', 'category'));
    }
}
