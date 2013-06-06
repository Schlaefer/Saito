<?php

	class TreeBehavior extends ModelBehavior {

		public function treeGetSubtree(Model $Model, $tree, $node_id) {
			$func = function (&$tree, &$entry, $node_id) {
						if ((int)$entry['Entry']['id'] === (int)$node_id) {
							$tree = array($entry);
							return 'break';
						}
					};
			Entry::mapTreeElements($tree, $func, $node_id);
			return $tree;
		}

		public function treeBuild(Model $Model, $threads) {
			$tree = array();
			foreach ($threads as $thread) {
				$id = $thread[$Model->alias]['id'];
				$pid = $thread[$Model->alias]['pid'];
				$tree[$id] = isset($tree[$id]) ? $thread + $tree[$id] : $thread;
				$tree[$pid]['_children'][] = &$tree[$id];
			}

			$sorted_trees = $this->_sortTreesAfterTime($tree[0]['_children']);

			return $sorted_trees;
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
			return ($a['Entry']['time'] > $b['Entry']['time']) ? 1 : -1;
		}

	}
