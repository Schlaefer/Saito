<?php

namespace App\Model\Behavior;

use Cake\ORM\Behavior;

class TreeBehavior extends Behavior
{
    /**
     * build tree
     *
     * @param array $postings postings
     * @return array
     */
    public function treeBuild($postings)
    {
        $tree = [];
        foreach ($postings as $posting) {
            $id = $posting['id'];
            $pid = $posting['pid'];
            $tree[$id] = isset($tree[$id]) ? $tree[$id] + $posting : $posting;
            $tree[$pid]['_children'][] = &$tree[$id];
        }

        // both boil down to 'first entry in $tree array', but let's be clear what
        // is expected in $tree
        if (isset($tree[0])) {
            // $postings had root entry/-ies with $pid = 0
            $tree = $tree[0]['_children'];
        } else {
            // $postings are subtree: assume lowest $id is root for subtree
            $tree = [reset($tree)];
        }

        // It's possible to do uasort before tree build and  get the same results,
        // without _sortTreesAfterTime
        // but then *all* entries have to be sorted whereas now only subthreads with childs
        // are sorted. So using _sortTreesAfterTime is actually faster in praxis.
        $_sortedTrees = $this->_sortTreesAfterTime($tree);

        return $_sortedTrees;
    }

    /**
     * Sort all entries in trees after time
     *
     * @param array $in array with trees
     * @param int $level level
     * @return array
     */
    protected function _sortTreesAfterTime($in, $level = 0)
    {
        if ($level > 0) {
            uasort($in, [$this, '_sort']);
        }

        foreach ($in as $k => $v) {
            if (isset($v['_children'])) {
                $in[$k]['_children'] = $this->_sortTreesAfterTime(
                    $v['_children'],
                    $level + 1
                );
            }
        }
        return $in;
    }

    /**
     * Sorter
     *
     * @param array $a a
     * @param array $b b
     * @return int
     */
    protected function _sort($a, $b)
    {
        if ($a['time'] === $b['time']) {
            return ($a['id'] > $b['id']) ? 1 : -1;
        } else {
            return ($a['time'] > $b['time']) ? 1 : -1;
        }
    }
}
