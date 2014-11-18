<?php

namespace Saito\User;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Saito\RememberTrait;
use Saito\User;
use Stopwatch\Lib\Stopwatch;

class Categories
{

    use RememberTrait;

    protected $_User;

    public function __construct(User\ForumsUserInterface $User)
    {
        $this->_User = $User;
    }

    /**
     * get all available categories to the user
     *
     *
     * @param string $format
     * - 'short': [id1 => 'id1', id2 => 'id2']
     * - 'select': [id1 => 'title 1'] for html select
     * @return mixed
     */
    public function getAll($action, $format = 'short')
    {
        Stopwatch::start('User\Categories::getAll()');
        $key = $action . '.' . $format;
        $categories = $this->remember($key, function () use ($action, $format) {
            $Categories = TableRegistry::get('Categories');
            $all = $Categories->getAllCategories();
            $categories = [];
            foreach ($all as $category) {
                $categories[$category->get('id')] = $category->get('category');
            }
            switch ($format) {
                case 'short':
                    $cIds = array_keys($categories);
                    $categories = array_combine($cIds, $cIds);
                    break;
            }

            return $this->filterAllowed($action, $categories);
        });
        Stopwatch::stop('User\Categories::getAll()');

        return $categories;
    }

    /**
     * return all categories based on the user preference
     *
     * @return array
     */
    public function getCurrent($action)
    {
        if (!$this->isCustomAllowed()) {
            return $this->getAll($action);
        }
        switch ($this->getType()) {
            case 'custom':
                return $this->getCustom($action);
            case 'single':
                $category = (int)$this->_User->get('user_category_active');
                $categories = [$category => $category];

                return $this->filterAllowed($action, $categories);
            case 'all':
                return $this->getAll($action);
            default:
                throw new \RuntimeException("Can't get user categories.",
                    1433849220);
        }
    }

    /**
     * return all categories in current category set
     *
     * @return array
     */
    public function getCustom($action)
    {
        // add new categories to custom set
        //
        // [4 => true, 7 => '0'] + [4 => '4', 7 => '7', 13 => '13']
        // becomes
        // [4 => true, 7 => '0', 13 => '13']
        // with 13 => '13' trueish

        $custom = $this->_User->get('user_category_custom');
        $custom = $this->filterAllowed($action, $custom);
        if (empty($custom)) {
            return [];
        }
        $custom = $custom + $this->getAll($action);

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
     * check if category customizaiton is available
     *
     * @return bool
     */
    protected function isCustomAllowed()
    {
        return $this->remember('isCustomAllowed', function () {
            if (!$this->_User->isLoggedIn()) {
                return false;
            }
            $globalActivation = Configure::read('Saito.Settings.category_chooser_global');
            if (!$globalActivation) {
                if (!Configure::read('Saito.Settings.category_chooser_user_override')) {
                    return false;
                }
                if (!$this->_User->get('user_category_override')) {
                    return false;
                }
            }

            return true;
        });
    }


    protected function filterAllowed($action, $categories)
    {
        // filter existing
        foreach ($categories as $categoryId => $value) {
            $resource = 'saito.core.category.' . $categoryId . '.' . $action;
            if (!$this->_User->permission($resource)) {
                unset($categories[$categoryId]);
            }
        }
        return $categories;
    }


}
