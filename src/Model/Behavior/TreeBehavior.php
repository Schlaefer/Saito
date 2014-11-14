<?php

	class TreeBehavior extends ModelBehavior {

		public function treeGetSubtree(Model $Model, $tree, $_nodeId) {
			$func = function (&$tree, &$entry, $_nodeId) {
				if ((int)$entry['Entry']['id'] === (int)$_nodeId) {
					$tree = array($entry);
					return 'break';
				}
			};
			Entry::mapTreeElements($tree, $func, $_nodeId);
			return $tree;
		}

		public function treeBuild(Model $Model, $threads) {
			$tree = array();
			foreach ($threads as $thread) {
				$id = $thread[$Model->alias]['id'];
				$pid = $thread[$Model->alias]['pid'];
				$tree[$id] = isset($tree[$id]) ? $tree[$id] + $thread : $thread;
				$tree[$pid]['_children'][] = &$tree[$id];
			}

			// It's possible to do uasort before tree build and  get the same results,
			// without _sortTreesAfterTime
			// but then *all* entries have to be sorted whereas now only subthreads with childs
			// are sorted. So using _sortTreesAfterTime is actually faster in praxis.
			$_sortedTrees = $this->_sortTreesAfterTime($tree[0]['_children']);

			return $_sortedTrees;
		}

/**
 * Sort all entries in trees after time
 *
 * @param array $in array with trees
 * @param int $level
 * @return array
 */
		protected function _sortTreesAfterTime($in, $level = 0) {
			if ($level > 0) {
				uasort($in, [$this, '_sort']);
			}

			foreach ($in as $k => $v) {
				if (isset($v['_children'])) {
					$in[$k]['_children'] = $this->_sortTreesAfterTime($v['_children'], $level + 1);
				}
			}
			return $in;
		}

		protected function _sort($a, $b) {
			if ($a['Entry']['time'] === $b['Entry']['time']) {
				return ($a['Entry']['id'] > $b['Entry']['id']) ? 1 : -1;
			} else {
				return ($a['Entry']['time'] > $b['Entry']['time']) ? 1 : -1;
			}
		}

	}
