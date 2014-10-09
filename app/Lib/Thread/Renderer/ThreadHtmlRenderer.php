<?php

	App::uses('HtmlRendererAbstract', 'Lib/Thread/Renderer');

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
			$this->_webroot = $this->_EntryHelper->webroot;
			if (isset($options['lineCache'])) {
				$this->_LineCache = $options['lineCache'];
			}
		}

		protected function _renderCore(PostingInterface $node) {
			$posting = $node->getRaw();
			$level = $node->getLevel();
			$id = (int)$posting['Entry']['id'];

			$threadLine = $this->_renderThreadLine($posting, $level);
			$css = $this->_css($node);
			$style = '';

			$params = $this->_EntryHelper->_View->Layout->requestCallback(
				'Request.Saito.ThreadLine.beforeRender',
				$this->_EntryHelper->_View,
				[
					'node' => $node,
					'css' => $css,
					'style' => '',
				],
				true
			);

			if (empty($params)) {
				$params = [];
			}

			$params += [
				'append' => '',
				'css' => $css,
				'prepend' => '',
				'style' => $style
			];

			//= manual json_encode() for performance
			$tid = (int)$posting['Entry']['tid'];
			$isNew = $node->isNew() ? 'true' : 'false';
			$jsData = <<<EOF
{"id":{$id},"new":{$isNew},"tid":{$tid}}
EOF;

			// data-id still used to identify parent when inserting	an inline-answered entry
			$out = <<<EOF
<li class="threadLeaf {$params['css']}" style="{$params['style']}" data-id="{$id}" data-leaf='{$jsData}'>
	<div class="threadLine">
		<button class="btnLink btn_show_thread threadLine-pre et">
			<i class="fa fa-thread"></i>
		</button>
		<a href="{$this->_webroot}entries/view/{$id}"
			class="link_show_thread et threadLine-content">
				{$params['prepend']}{$threadLine}{$params['append']}
		</a>
	</div>
</li>
EOF;
			return $out;
		}

		protected function _renderThreadLine(array $posting, $level) {
			$id = (int)$posting['Entry']['id'];
			$useLineCache = $level > 0 && $this->_LineCache;

			if ($useLineCache && $threadLine = $this->_LineCache->get($id)) {
				return $threadLine;
			}

			$subject = $this->getSubject($posting);
			$badges = $this->getBadges($posting);
			$username = h($posting['User']['username']);
			$time = $this->_EntryHelper->TimeH->formatTime($posting['Entry']['time']);

			//= category HTML
			$category = '';
			if ($level === 0) {
				$accession = $posting['Category']['accession'];
				if (!isset(self::$_catL10n[$accession])) {
					$catAcs = h(__d('nondynamic', 'category_acs_' . $accession . '_exp'));
					$catDesc = h($posting['Category']['description']);
					$catTitle = h($posting['Category']['category']);
					$category = <<<EOF
<span class="c-category acs-$accession" title="$catDesc ($catAcs)">
	($catTitle)
</span>
EOF;
					self::$_catL10n[$accession] = $category;
				}
				$category = self::$_catL10n[$accession];
			}

			$threadLine = <<<EOF
{$subject}
<span class="c-username"> – {$username}</span>
{$category}
<span class="threadLine-post"> {$time} {$badges} </span>
EOF;

			if ($useLineCache) {
				$this->_LineCache->set($id, $threadLine, $this->_lastAnswer);
			}
			return $threadLine;
		}

	}
