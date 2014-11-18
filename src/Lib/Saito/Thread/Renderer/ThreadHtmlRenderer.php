<?php

	namespace Saito\Thread\Renderer;

	/**
	 * Class ThreadHtmlRenderer renders postings into a tree
	 */
	class ThreadHtmlRenderer extends HtmlRendererAbstract {

		/**
		 * @var array performance-cheat for category l10n
		 */
		protected static $_catL10n = [];

		/**
		 * @var ItemCache
		 */
		protected $_LineCache;

		protected $_webroot;

		public function setOptions($options) {
			parent::setOptions($options);
			$this->_webroot = $this->_Helper->request->webroot;
			if (isset($options['lineCache'])) {
				$this->_LineCache = $options['lineCache'];
			}
		}

		protected function _renderCore(\Saito\Posting\PostingInterface $node) {
			$posting = $node->getRaw();
			$level = $node->getLevel();
			$id = $posting['Entry']['id'];

			$threadLine = $this->_renderThreadLine($node, $posting, $level);
			$css = $this->_css($node);
			$badges = $this->getBadges($node);

			$requestedParams = $this->_SEM->dispatch(
				'Request.Saito.View.ThreadLine.beforeRender',
				['node' => $node, 'View' => $this->_View]
			);

			$params = ['css' => '', 'style' => ''];

			if (!empty($requestedParams) && !empty($requestedParams[0])) {
				foreach ($requestedParams as $param) {
					foreach ($param as $key => $value) {
						$params[$key] .= $value;
					}
				}
			}

			//= manual json_encode() for performance
			$tid = $posting['Entry']['tid'];
			$isNew = $node->isUnread() ? 'true' : 'false';
			$jsData = <<<EOF
{"id":{$id},"new":{$isNew},"tid":{$tid}}
EOF;

			// data-id still used to identify parent when inserting	an inline-answered entry
			// last </span> comes from _renderThreadLine and allows appending to threadLine-post
			$out = <<<EOF
<li class="threadLeaf {$css}" data-id="{$id}" data-leaf='{$jsData}'>
	<div class="threadLine {$params['css']}" style="{$params['style']}">
		<button class="btnLink btn_show_thread threadLine-pre et">
			<i class="fa fa-thread"></i>
		</button>
		<a href="{$this->_webroot}entries/view/{$id}"
			class="link_show_thread et threadLine-content">
				{$threadLine} {$badges} </span>
		</a>
	</div>
</li>
EOF;
			return $out;
		}

		protected function _renderThreadLine($node, array $posting, $level) {
			$id = $posting['Entry']['id'];
			$useLineCache = $level > 0 && $this->_LineCache;

			if ($useLineCache && $threadLine = $this->_LineCache->get($id)) {
				return $threadLine;
			}

			$subject = $this->getSubject($node);
			$username = h($posting['User']['username']);
			$time = $this->_Helper->TimeH->formatTime($posting['Entry']['time']);

			//= category HTML
			$category = '';
			if ($level === 0) {
				$categoryId = $posting['Category']['id'];
				if (!isset(self::$_catL10n[$categoryId])) {
					$accession = $posting['Category']['accession'];
					$catAcs = h(__d('nondynamic', 'category_acs_' . $accession . '_exp'));
					$catDesc = h($posting['Category']['description']);
					$catTitle = h($posting['Category']['category']);
					$category = <<<EOF
<span class="c-category acs-$accession" title="$catDesc ($catAcs)">
	($catTitle)
</span>
EOF;
					self::$_catL10n[$categoryId] = $category;
				}
				$category = self::$_catL10n[$categoryId];
			}

			// last </span> closes in parent
			$threadLine = <<<EOF
{$subject}
<span class="c-username"> – {$username}</span>
{$category}
<span class="threadLine-post"> {$time}
EOF;

			if ($useLineCache) {
				$this->_LineCache->set($id, $threadLine, $this->_lastAnswer);
			}
			return $threadLine;
		}

	}
