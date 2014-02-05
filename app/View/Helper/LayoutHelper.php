<?php

	App::uses('AppHelper', 'View/Helper');

	class LayoutHelper extends AppHelper {

		public $helpers = [
				'Html'
		];

		protected $_themeImgUrl = null;

		public function beforeRender($viewFile) {
			$this->_themeImgUrl = $this->request->webroot . 'theme' . DS . $this->theme .
					DS . Configure::read('App.imageBaseUrl');
		}

		public function beforeLayout($layoutFile) {
			// @todo
			if (strtolower($this->_View->theme) !== 'default') {
				return;
			}
			$stylesheets =
					[
							'stylesheets/static.css',
							'stylesheets/styles.css'
					];
			if (Configure::read('debug')) {
				$stylesheets[] = 'stylesheets/cake.css';
			}
			$this->Html->css($stylesheets, null, ['inline' => false]);
		}

		public function jQueryTag() {
			$url = 'dist/';
			$name = 'jquery';
			if ((int)Configure::read('debug') === 0) {
				$name = $name . '.min';
			}
			return $this->Html->script($this->Html->assetUrl($url . $name,
					['ext' => '.js', 'fullBase' => true]));
		}

		/**
		 * Output link to Android touch icon
		 */
		public function androidTouchIcon($size, array $options = []) {
			return $this->_touchIcon($size,
					$options + ['rel' => 'shortcut icon']);
		}

		/**
		 * Output link to iOS touch icon
		 */
		public function appleTouchIcon($size, array $options = []) {
			return $this->_touchIcon($size, $options);
		}

		/**
		 * Output link to touch icon
		 *
		 * Files must be placed in <theme>/webroot/img/<baseName>-<size>x<size>.png
		 *
		 * @param mixed $size integer or array with integer
		 * @param array $options
		 *  `baseName` (default: 'app-icon')
		 *  `precomposed` adds '-precomposed' to baseName (default: false)
		 *  `rel` (default: 'apple-touch-icon')
		 *  `size` outputs "size"-attribute (default: true)
		 * @return string
		 */
		protected function _touchIcon($size, array $options = []) {
			if (is_array($size)) {
				$_out = '';
				foreach ($size as $s) {
					$_out .= $this->appleTouchIcon($s, $options);
				}
				return $_out;
			}

			$_defaults = [
					'baseName' => 'app-icon',
					'precomposed' => false,
					'rel' => 'apple-touch-icon',
					'size' => true
			];
			$options += $_defaults;

			$_xSize = "{$size}x{$size}";

			$_basename = $options['baseName'];
			if ($options['precomposed']) {
				$_basename .= '-precomposed';
			}
			$_filename = "{$_basename}-{$_xSize}.png";

			$url = "{$this->_themeImgUrl}{$_filename}";

			$_out = "<link rel=\"{$options['rel']}\" ";
			if ($options['size']) {
				$_out .= "size=\"{$_xSize}\" ";
			}
			$_out .= "href=\"{$url}\">";
			return $_out;
		}

		/**
		 * Generates page heading html
		 *
		 * @param string $heading
		 * @param string $tag
		 * @return string
		 */
		public function pageHeading($heading, $tag = 'h1') {
			return $this->Html->tag($tag, $heading, ['class' => 'pageHeading']);
		}

		/**
		 * Generates intoText tag
		 *
		 * @param string $content
		 * @return string
		 */
		public function infoText($content) {
			return $this->Html->tag('span', $content, ['class' => 'infoText']);
		}

		public function dropdownMenuButton(array $menuItems, array $options = []) {
			$options += ['class' => ''];
			$_divider = '<li class="dropdown-divider"></li>';
			$_menu = '';
			foreach ($menuItems as $_menuItem) {
				if ($_menuItem === 'divider') {
					$_menu .= $_divider;
				} else {
					$_menu .= "<li>$_menuItem</li>";
				}
			}
			$_id = AppHelper::tagId();
			$_button = $this->Html->tag(
					'button',
					'<i class="fa fa-wrench"></i>&nbsp;<i class="fa fa-caret-down"></i>',
					$options + [
							'escape' => false,
							'onclick' => "$(this).dropdown('attach', '#d$_id');"
					]);
			$_out = <<<EOF
				$_button
				<div id="d$_id" class="dropdown-relative dropdown dropdown-tip">
					<ul class="dropdown-menu">
							$_menu
					</ul>
				</div>
EOF;
			return $_out;
		}

		public function panelHeading($content, array $options = []) {
			$options += [
					'class' => 'panel-heading',
					'pageHeading' => false,
					'tag' => 'h2'
			];
			if ($options['pageHeading']) {
				$options['class'] .= ' pageTitle';
				$options['tag'] = 'h1';
			}
			if (is_string($content)) {
				$content = ['middle' => $content];
			}
			$content['middle'] =  "<{$options['tag']}>{$content['middle']}</{$options['tag']}>";
			return $this->heading($content, $options);
		}

		public function heading($content, array $options = []) {
			$options += ['class' => ''];
			if (is_string($content)) {
				$_content = ['middle' => $content];
			} else {
				$_content = $content;
			}
			$_content += ['first' => '', 'middle' => '', 'last' => ''];
			return <<<EOF
				<div class="{$options['class']} heading-3">
					<div class='heading-3-first'>{$_content['first']}</div>
					<div class='heading-3-middle'>{$_content['middle']}</div>
					<div class='heading-3-last'>{$_content['last']}</div>
				</div>
EOF;
		}

	}
