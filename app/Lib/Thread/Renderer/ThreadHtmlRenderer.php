<?php

	App::uses('PostingViewTrait', 'Lib/Thread');

	class ThreadHtmlRenderer {

		use PostingViewTrait;

		/**
		 * performance-cheat for category l10n
		 *
		 * @var array
		 */
		protected static $_catL10n = [];

		protected $_node;

		protected $_CurrentUser;

		protected $_EntryHelper;

		protected $_settings;

		protected $_LineCache;

		protected $_lastAnswer;

		protected $_maxThreadDepthIndent;

		public function __construct($node, EntryHHelper $EntryHelper, $options = []) {
			$this->_node = $node;
			$this->_lastAnswer = strtotime($node->Thread->get('root')->getRaw()['Entry']['last_answer']);
			$this->_EntryHelper = $EntryHelper;
			// @todo should be injected
			$this->_LineCache = $this->_EntryHelper->_View->get('LineCache');

			$this->_maxThreadDepthIndent = $options['maxThreadDepthIndent'];
			$this->_settings = $options + ['ignore' => null, 'currentEntry' => null];
		}

		public function render() {
			$html = $this->_renderNode($this->_node);
			if ($this->_node->getLevel() === 0) {
				$html = $this->_wrapUl($html, 0, $this->_node->id);
			}
			return $html;
		}

		protected function _renderNode($node) {
			$data = $node->getRaw();
			$level = $node->getLevel();

			$html = $this->_renderCore($data, $level, $node);

			$children = $node->getChildren();
			if (empty($children)) {
				return $html;
			}

			$childrenHtml = '';
			foreach ($node->getChildren() as $child) {
				$childrenHtml .= $this->_renderNode($child);
			}
			$html .= '<li>' . $this->_wrapUl($childrenHtml, $level + 1) . '</li>';
			return $html;
		}

		protected function _renderCore($data, $level, $node) {
			$id = (int)$data['Entry']['id'];

			$useLineCache = $level > 0;
			if ($useLineCache) {
				$_threadLineCached = $this->_LineCache->get($id);
			}
			if (empty($_threadLineCached)) {
				$_threadLineCached = $this->_threadLineCached($data, $level);
				if ($useLineCache) {
					$this->_LineCache->set($id, $_threadLineCached, $this->_lastAnswer);
				}
			}

			$isNew = $node->isNew();
			$_currentlyViewed = ($this->_settings['currentEntry'] !== null &&
				$this->_EntryHelper->params['action'] === 'view') ? $this->_settings['currentEntry'] : null;
			$css = $this->_generateEntryTypeCss($level, $isNew,
				$data['Entry']['id'], $_currentlyViewed);
			if ($node->isIgnored()) {
				$css .= ' ignored';
			}

			//= manual json_encode() for performance
			$tid = (int)$data['Entry']['tid'];
			$isNew = $isNew ? 'true' : 'false';
			$leafData = <<<EOF
{"id":{$id},"new":{$isNew},"tid":{$tid}}
EOF;

			// data-id still used to identify parent when inserting	an inline-answered entry
			$out = <<<EOF
<li class="threadLeaf {$css}" data-id="{$id}" data-leaf='{$leafData}'>
	<div class="threadLine">
		<button class="btnLink btn_show_thread threadLine-pre et">
			<i class="fa fa-thread"></i>
		</button>
		<a href="{$this->_EntryHelper->webroot}entries/view/{$id}"
			class="link_show_thread et threadLine-content">
				{$_threadLineCached}
		</a>
	</div>
</li>
EOF;
			return $out;
		}

		/**
		 *
		 *
		 * Everything you do in here is in worst case done a few hundred times on
		 * the frontpage. Think about (and benchmark) performance before you change it.
		 */
		protected function _threadLineCached(array $entrySub, $level) {
			$timestamp = $entrySub['Entry']['time'];

			$category = '';
			if ($level === 0) {
				$accession = $entrySub['Category']['accession'];
				if (!isset(self::$_catL10n[$accession])) {
					self::$_catL10n[$accession] = __d('nondynamic', 'category_acs_' . $accession . '_exp');
				}
				$categoryTitle = self::$_catL10n[$accession];
				$category = '<span class="c-category acs-' . $accession . '"
            title="' . $entrySub['Category']['description'] . ' ' . ($categoryTitle) . '">
        (' . $entrySub['Category']['category'] . ')
      </span>';
			}

			// normal time output
			$time = $this->_EntryHelper->TimeH->formatTime($timestamp);

			$subject = $this->getSubject($entrySub);
			$badges = $this->getBadges($entrySub);
			$username = h($entrySub['User']['username']);

			// wrap everything up
			$out = <<<EOF
{$subject}
<span class="c-username"> – {$username}</span>
{$category}
<span class="threadLine-post"> {$time} {$badges} </span>
EOF;
			return $out;
		}

		/**
		 * Wraps li tags with ul tag
		 *
		 * @param string $string li html list
		 * @param $level
		 * @param $id
		 * @return string
		 */
		protected function _wrapUl($string, $level = null, $id = null) {
			if ($level >= $this->_maxThreadDepthIndent) {
				return $string;
			}

			$class = 'threadTree-node';
			$data = '';
			if ($level === 0) {
				$class .= ' root';
				$data = 'data-id="' . $id . '"';
			}
			return "<ul {$data} class=\"{$class}\">{$string}</ul>";
		}

		protected function _generateEntryTypeCss($level, $new, $current, $viewed = null) {
			$entryType = ($level === 0) ? 'et-root' : 'et-reply';
			if ($new) {
				$entryType .= ' et-new';
			} else {
				$entryType .= ' et-old';
			}
			if (!empty($viewed)) {
				if ($current === $viewed) {
					$entryType .= ' et-current';
				}
			}
			return $entryType;
		}

	}
