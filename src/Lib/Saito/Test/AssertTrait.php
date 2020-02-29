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
     * @param string|array $expected expected
     * @param string $result HTML
     * @param int $count How often the tag is expected.
     * @return void
     */
    public function assertContainsTag($expected, string $result, int $count = 1): void
    {
        if (is_string($expected)) {
            $expected = [$expected => []];
        }

        do {
            $crawler = new Crawler();
            $crawler->addHtmlContent($result);
            $selector = key($expected);
            $node = $crawler->filter($selector);
            $this->assertEquals(
                $count,
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
     * Assert result does not contain tag
     * @param string $selector Tag as CSS selector query
     * @param string $result HTML result to check
     * @return void
     */
    public function assertNotContainsTag(string $selector, string $result): void
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($result);
        $node = $crawler->filter($selector);
        $this->assertEquals(
            0,
            $node->count(),
            "Selector '$selector' was not expected to be found."
        );
    }

    /**
     * Assert Flash message was set
     *
     * @param string $message message
     * @param string $element element
     * @param bool $debug debugging
     * @return void
     */
    protected function assertFlash(string $message, ?string $element = null, $debug = false): void
    {
        if ($debug) {
            debug($_SESSION['Flash']['flash']);
        }
        if (!empty($_SESSION['Flash']['flash'])) {
            foreach ($_SESSION['Flash']['flash'] as $flash) {
                if ($flash['message'] !== $message) {
                    continue;
                }
                if ($element !== null && $flash['element'] !== 'flash/' . $element) {
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
