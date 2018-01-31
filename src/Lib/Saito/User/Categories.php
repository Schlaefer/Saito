<?php

namespace Saito\User;

use App\Model\Table\CategoriesTable;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Saito\RememberTrait;
use Saito\User;
use Saito\User\ForumsUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Handle categories for user.
 *
 * @package Saito\User
 */
class Categories
{
    use RememberTrait;

    /**
     * @var ForumsUserInterface
     */
    protected $_User;

    /**
     * Constructor.
     *
     * @param ForumsUserInterface $User Current-User
     */
    public function __construct(User\ForumsUserInterface $User)
    {
        $this->_User = $User;
    }

    /**
     * get all available categories to the user
     *
     *
     * @param string $action action
     * @param string $format format
     * - 'short': [id1 => 'id1', id2 => 'id2']
     * - 'select': [id1 => 'title 1'] for html select
     * @return mixed
     */
    public function getAll($action, $format = 'short')
    {
        Stopwatch::start('User\Categories::getAll()');
        $key = $action . '.' . $format;
        $categories = $this->remember(
            $key,
            function () use ($action, $format) {
                /* @var CategoriesTable $Categories */
                $Categories = TableRegistry::get('Categories');
                $all = $Categories->getAllCategories();
                $categories = [];
                /* @var Entity $category */
                foreach ($all as $category) {
                    $categories[$category->get('id')] = $category->get('category');
                }
                switch ($format) {
                    case 'short':
                        $cIds = array_keys($categories);
                        $categories = array_combine($cIds, $cIds);
                        break;
                }

                return $this->_filterAllowed($action, $categories);
            }
        );
        Stopwatch::stop('User\Categories::getAll()');

        return $categories;
    }

    /**
     * return all categories based on the user preference
     *
     * @param string $action action
     * @return array
     */
    public function getCurrent($action)
    {
        if (!$this->_isCustomAllowed()) {
            return $this->getAll($action);
        }
        switch ($this->getType()) {
            case 'custom':
                return $this->getCustom($action);
            case 'single':
                $category = (int)$this->_User->get('user_category_active');
                $categories = [$category => $category];

                return $this->_filterAllowed($action, $categories);
            case 'all':
                return $this->getAll($action);
            default:
                throw new \RuntimeException(
                    "Can't get user categories.",
                    1433849220
                );
        }
    }

    /**
     * return all categories in current category set
     *
     * @param string $action action
     * @return array
     */
    public function getCustom($action)
    {
        $custom = $this->_User->get('user_category_custom');
        if (empty($custom)) {
            $custom = [];
        }
        $custom = $custom + $this->getAll($action);
        $custom = $this->_filterAllowed($action, $custom);

        // add new categories to custom set
        //
        // [4 => true, 7 => '0'] + [4 => '4', 7 => '7', 13 => '13']
        // becomes
        // [4 => true, 7 => '0', 13 => '13']
        // with 13 => '13' trueish

        // then filter for zeros to get only the user categories
        //  [4 => true, 13 => '13']
        $custom = array_filter($custom);

        $keys = array_keys($custom);

        return array_combine($keys, $keys);
    }

    /**
     * get type of current user preference
     *
     * @return string
     */
    public function getType()
    {
        $active = (int)$this->_User->get('user_category_active');
        if ($active > 0) {
            return 'single';
        }
        if ($active === 0) {
            $custom = $this->_User->get('user_category_custom');
            if (!empty($custom)) {
                return 'custom';
            }
        }

        return 'all';
    }

    /**
     * Check if user as permission for action on category
     *
     * @param string $action action
     * @param int|Entity|array $category ID, category-array or category-entity
     * @return bool
     */
    public function permission($action, $category)
    {
        if ($category instanceof Entity) {
            $category = $category->get('id');
        } elseif (is_array($category)) {
            $category = $category['id'];
        }
        $resource = 'saito.core.category.' . $category . '.' . $action;

        return $this->_User->permission($resource);
    }

    /**
     * check if category customizaiton is available
     *
     * @return bool
     */
    protected function _isCustomAllowed()
    {
        return $this->remember(
            'isCustomAllowed',
            function () {
                if (!$this->_User->isLoggedIn()) {
                    return false;
                }
                $globalActivation = Configure::read(
                    'Saito.Settings.category_chooser_global'
                );
                if (!$globalActivation) {
                    if (!Configure::read(
                        'Saito.Settings.category_chooser_user_override'
                    )
                    ) {
                        return false;
                    }
                    if (!$this->_User->get('user_category_override')) {
                        return false;
                    }
                }

                return true;
            }
        );
    }

    /**
     * Filter out unallowed categories
     *
     * @param array $action action to filter for
     * @param array $categories categories to filter
     * @return array categories
     */
    protected function _filterAllowed($action, array $categories)
    {
        foreach ($categories as $categoryId => $value) {
            if (!$this->_User->Categories->permission($action, $categoryId)) {
                unset($categories[$categoryId]);
            }
        }

        return $categories;
    }
}
