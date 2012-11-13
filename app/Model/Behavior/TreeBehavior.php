<?php

	class TreeBehavior extends ModelBehavior {

		public function treeBuild(Model $Model, $threads) {
			$out = $this->_parseTreeInit($Model, $threads);
			$out = $this->_sortTime($out);
			return $out;
		}

		protected function _parseTreeInit(Model $Model, $threads) {
			$tree = array();
			foreach ($threads as $thread) {
				$this->_parseTreeRecursive($Model, $tree, $thread);
			}
			return $tree[0]['_children'];
		}

		protected function _parseTreeRecursive(Model $Model, &$tree, $item) {
			$id = $item[$Model->alias]['id'];
			$pid = $item[$Model->alias]['pid'];
			$tree[$id] = isset($tree[$id]) ? $item + $tree[$id] : $item;
			$tree[$pid]['_children'][] = &$tree[$id];
		}

		protected function _sortTime($in, $level = 0) {
			if ($level > 0) {
				$in = $this->_quicksort($in);
			}
			foreach ($in as $k => $v) {
				if (isset($v['_children'])) {
					$in[$k]['_children'] = $this->_sortTime($v['_children'], $level + 1);
				}
			}
			return $in;
		}

		/**
		 * bread and butter quicksort
		 */
		protected function _quicksort($in) {
			if (count($in) < 2)
				return $in;
			$left = $right = array(
					);

			reset($in);
			$pivot_key = key($in);
			$pivot = array_shift($in);

			foreach ($in as $k => $v) {
				if ($v['Entry']['time'] < $pivot['Entry']['time'])
					$left[$k] = $v;
				else
					$right[$k] = $v;
			}
			return array_merge($this->_quicksort($left),
					array($pivot_key => $pivot), $this->_quicksort($right));
		}

	}