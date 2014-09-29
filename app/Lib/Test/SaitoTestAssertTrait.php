<?php

	trait SaitoTestAssertTrait {

		/**
		 * tests if XPath exists in HTML Source
		 *
		 * @param $html HTML
		 * @param $path XPath
		 * @param int $count how many times should XPath exist in HTML
		 * @return mixed
		 */
		public function assertXPath($html, $path, $count = 1) {
			$xpath = $this->_getDOMXPath($html);
			$length = $xpath->query($path)->length;
			return $this->assertEquals($count, $length, "Failed XPath. Expected '$path' to be found $count times instead of $length.");
		}

		public function assertNotXPath($html, $path) {
			return !$this->assertXPath($html, $path, 0);
		}

		protected function _getDOMXPath($html) {
			$document = new DOMDocument;
			libxml_use_internal_errors(true);
			$document->loadHTML('<!DOCTYPE html>' . $html);
			$xpath = new DOMXPath($document);
			libxml_clear_errors();
			return $xpath;
		}

	}