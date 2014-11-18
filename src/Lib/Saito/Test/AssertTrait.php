<?php

	namespace Saito\Test;

    use Symfony\Component\DomCrawler\Crawler;

	trait AssertTrait {

        public function assertResponseContainsTags($expected)
        {
            $this->assertContainsTag($expected,
                $this->_controller->response->body());
        }

        /**
         * @param array $expected
         * @param string $result
         */
        public function assertContainsTag($expected, $result) {
            do {
                $crawler = new Crawler;
                $crawler->addHtmlContent($result);
                $selector = key($expected);
                $node = $crawler->filter($selector);
                $this->assertEquals(1, $node->count(), "Selector '$selector' not found.");

                if (isset($expected[$selector]['attributes'])) {
                    foreach ($expected[$selector]['attributes'] as $attribute => $value)  {
                        $this->assertEquals($value, $node->attr($attribute));
                    }
                }
            } while (next($expected));
        }

		/**
		 * tests if XPath exists in HTML Source
		 *
		 * @param string $html HTML
		 * @param string $path XPath
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
			$document = new \DOMDocument;
			libxml_use_internal_errors(true);
			$document->loadHTML('<!DOCTYPE html>' . $html);
			$xpath = new \DOMXPath($document);
			libxml_clear_errors();
			return $xpath;
		}

	}
