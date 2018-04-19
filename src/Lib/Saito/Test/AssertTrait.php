<?php

namespace Saito\Test;

use Cake\Http\Response;
use Cake\Routing\Router;
use Symfony\Component\DomCrawler\Crawler;

trait AssertTrait
{

    /**
     * Check that an redirect to the login is performed
     *
     * @param string $redirectUrl redirect URL '/where/I/come/from'
     * @param string $msg Message
     * @return void
     */
    public function assertRedirectLogin($redirectUrl = null, string $msg = '')
    {
        /** @var Response $response */
        $response = $this->_controller->response;
        $expected = Router::url([
            '_name' => 'login',
            'plugin' => false,
            '?' => ['redirect' => $redirectUrl]
        ], true);
        $redirectHeader = $response->getHeader('Location')[0];
        $this->assertEquals($expected, $redirectHeader, $msg);
    }

    /**
     * Check if only specific roles is allowed on action
     *
     * @param string $route URL
     * @param string $role role
     * @param true|string|null $referer true: same as $url, null: none, string: URL
     * @param string $method HTTP-method
     * @return void
     */
    public function assertRouteForRole($route, $role, $referer = true, $method = 'GET')
    {
        if ($referer === true) {
            $referer = $route;
        }
        $method = strtolower($method);
        $types = ['admin' => 3, 'mod' => 2, 'user' => 1, 'anon' => 0];

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
                $this->assertRedirectLogin($referer, "No login redirect for $role on $method $route");
            } else {
                $this->{$method}($route);
                $method = strtoupper($method);
                $this->assertNoRedirect("Redirect wasn't expected for user-role '$role' on $method $route");
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
            (string)$this->_controller->response->getBody()
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
