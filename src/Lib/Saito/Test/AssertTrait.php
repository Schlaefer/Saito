<?php

namespace Saito\Test;

use Symfony\Component\DomCrawler\Crawler;

trait AssertTrait
{
    /**
     * Check if only specific roles is allowed on action
     *
     * @param string $route URL
     * @param string $role role
     * @param string $method HTTP-method
     * @return void
     */
    public function assertRouteForRole($route, $role, $method = 'get')
    {
        $method = strtolower($method);
        $types = ['admin' => 3 , 'mod' => 2, 'user' => 1, 'anon' => 0];

        foreach ($types as $title => $type) {
            switch ($title) {
                case 'anon':
                    break;
                case 'user':
                    $this->_loginUser(3);
                    break;
                case 'mod':
                    $this->_loginUser(2);
                    break;
                case 'admin':
                    $this->_loginUser(1);
                    break;
            }

            if ($type < $types[$role]) {
                $this->{$method}($route);
                $method = strtoupper($method);
                $this->assertRedirect('/login', "No login redirect for $role on $method $route");
            } else {
                $this->{$method}($route);
                $this->assertNoRedirect();
            }
        }
    }

    /**
     * assert contains tags
     *
     * @param array $expected expected
     * @return void
     */
    public function assertResponseContainsTags($expected)
    {
        $this->assertContainsTag(
            $expected,
            $this->_controller->response->body()
        );
    }

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
            $crawler = new Crawler;
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
        $document = new \DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html>' . $html);
        $xpath = new \DOMXPath($document);
        libxml_clear_errors();

        return $xpath;
    }
}
