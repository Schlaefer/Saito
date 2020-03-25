<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test;

use AssertionError;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Non-depended assumptions
 */
trait AssertTrait
{
    /**
     * assert contains tag
     *
     * @param array $expected expected
     * @param string $result HTML
     * @return void
     */
    public function assertContainsTag($expected, $result)
    {
        do {
            $crawler = new Crawler();
            $crawler->addHtmlContent($result);
            $selector = key($expected);
            $node = $crawler->filter($selector);
            $this->assertEquals(
                1,
                $node->count(),
                "Selector '$selector' not found."
            );

            if (isset($expected[$selector]['attributes'])) {
                foreach ($expected[$selector]['attributes'] as $attribute => $value) {
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
    public function assertXPath($html, $path, $count = 1)
    {
        $xpath = $this->_getDOMXPath($html);
        $length = $xpath->query($path)->length;

        return $this->assertEquals(
            $count,
            $length,
            "Failed XPath. Expected '$path' to be found $count times instead of $length."
        );
    }

    /**
     * assert not xpath
     *
     * @param string $html path
     * @param string $path path
     * @return bool
     */
    public function assertNotXPath($html, $path)
    {
        return !$this->assertXPath($html, $path, 0);
    }

    /**
     * get dom xpath
     *
     * @param string $html HTML
     * @return \DOMXPath
     */
    protected function _getDOMXPath($html)
    {
        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html>' . $html);
        $xpath = new \DOMXPath($document);
        libxml_clear_errors();

        return $xpath;
    }

    /**
     * Assert Flash message was set
     *
     * @param string $message message
     * @param string $element element
     * @param bool $debug debugging
     * @return void
     */
    protected function assertFlash(string $message, string $element = null, $debug = false): void
    {
        if ($debug) {
            debug($_SESSION['Flash']['flash']);
        }
        if (!empty($_SESSION['Flash']['flash'])) {
            foreach ($_SESSION['Flash']['flash'] as $flash) {
                if ($flash['message'] !== $message) {
                    continue;
                }
                if ($element !== null && $flash['element'] !== 'Flash/' . $element) {
                    continue;
                }

                return;
            }
        }

        throw new AssertionError(
            sprintf('Flash message "%s" was not set.', $message)
        );
    }
}
