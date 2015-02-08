<?php

	namespace Phile\Plugin\Siezi\PhileMarkdownEditor;

	use Phile\Exception;
	use Phile\Repository\Page;

	/**
	 * Class ContentFile
	 *
	 * @author Schlaefer <openmail+sourcecode@siezi.com>
	 * @link https://github.com/Schlaefer/phileMarkdownEditor
	 * @license http://opensource.org/licenses/MIT
	 * @package Phile\Plugin\Siezi\PhileMarkdownEditor
	 */
	class ContentFile {

		/**
		 * @var content directory
		 */
		protected $_contentDir;

		/**
		 * @var string path relative to content folder
		 */
		protected $_filepath;

		/**
		 * @var string full path
		 */
		protected $_fullPath;

		public function __construct($filename = null) {
			$this->_contentDir = CONTENT_DIR;
			$this->_filepath = $filename;
		}

		public function create($title, $content) {
			if (empty($title)) {
				throw new \InvalidArgumentException;
			}

			$path = explode('/', $title);
			$name = array_pop($path);
			if (empty($name)) {
				throw new Exception("Empty name '$name' not allowed.");
			}

			//= add into existing subfolders
			if (count($path)) {
				$sub = '';
				foreach ($path as $element) {
					$sub .= $this->_slug($element) . DIRECTORY_SEPARATOR;
				}
				if (!file_exists($this->_contentDir . $sub)) {
					throw new Exception("Subfolder '$sub' does not exist.");
				}
				$name = $sub . $name;
			}

			$this->_filepath = $name;
			$this->_fullPath = $this->_contentDir . $this->_filepath . CONTENT_EXT;

			if ($this->exists()) {
				throw new Exception("File '$this->_filepath' already exists.");
			}

			file_put_contents($this->_getFullPath(), $content);
		}

		public function getFilename() {
			return $this->_filepath;
		}

		public function getFullPath() {
			return $this->_getFullPath();
		}

		public function exists() {
			return file_exists($this->_getFullPath());
		}

		public function delete() {
			unlink($this->_file());
		}

		public function read() {
			return file_get_contents($this->_file());
		}

		public function write($content) {
			if (!file_put_contents($this->_file(), $content)) {
				throw new Exception();
			}
		}

		protected function _file() {
			$file = $this->_getFullPath();
			if (!file_exists($file)) {
				throw new \Exception;
			}
			return $file;
		}

		protected function _getFullPath() {
			if (isset($this->_fullPath)) {
				return $this->_fullPath;
			}

			// filename for root index is empty string
			if (!is_string($this->_filepath) && empty($this->_filepath)) {
				throw new \RuntimeException('Filename not set');
			}

			$PageRepository = new Page();
			$this->_fullPath = $PageRepository->findByPath($this->_filepath)
				->getFilePath();
			return $this->_fullPath;
		}

		protected function _slug($text) {
			// replace non letter or digits by -
			$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

			// trim
			$text = trim($text, '-');

			// transliterate
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

			// lowercase
			$text = strtolower($text);

			// remove unwanted characters
			$text = preg_replace('~[^-\w]+~', '', $text);

			if (empty($text)) {
				return 'n-a';
			}

			return $text;
		}

	}
