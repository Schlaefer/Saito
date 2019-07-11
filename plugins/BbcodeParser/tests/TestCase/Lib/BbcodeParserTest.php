<?php

namespace BbcodeParser\Test\Lib;

use App\View\Helper\ParserHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\View\View;
use Plugin\BbcodeParser\Lib;
use Plugin\BbcodeParser\src\Lib\Parser;
use Saito\Markup\MarkupSettings;
use Saito\Test\SaitoTestCase;
use Saito\User\Userlist;
use Saito\User\Userlist\UserlistModel;

class BbcodeParserTest extends SaitoTestCase
{

    /**
     * @var Parser
     */
    protected $_Parser = null;

    /** @var MarkupSettings */
    protected $MarkupSettings;

    public function testBold()
    {
        $input = '[b]bold[/b]';
        $expected = ['strong' => [], 'bold', '/strong'];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testEmphasis()
    {
        $input = '[i]italic[/i]';
        $expected = ['em' => [], 'italic', '/em'];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testQuoteblock()
    {
        $input = '[quote]foo bar[/quote]';
        $expected = ['blockquote' => [], 'foo bar', '/blockquote'];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testStrike()
    {
        $expected = ['del' => [], 'text', '/del'];

        // [strike]
        $input = '[strike]text[/strike]';
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // [s]
        $input = '[s]text[/s]';
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testSpoiler()
    {
        $input = 'pre [spoiler] te "\' xt[/spoiler]';
        $expected = [
            'pre',
            [
                'div' => [
                    'class' => 'richtext-spoiler',
                    'style' => 'display: inline;'
                ]
            ],
            ['script' => true],
            'preg:/(.*?)"string":" te &quot;&#039; xt"(.*?)(?=<)/',
            '/script',
            [
                'a' => [
                    'href' => '#',
                    'class' => 'richtext-spoiler-link',
                    'onclick'
                ]
            ],
            'preg:/.*▇ Spoiler ▇.*?(?=<)/',
            '/a',
            '/div'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testList()
    {
        $input = "[list]\n[*]fooo\n[*]bar\n[/list]";
        $expected = [
            ['ul' => []],
            ['li' => []],
            'fooo',
            ['br' => []],
            '/li',
            ['li' => []],
            'bar',
            ['br' => []],
            '/li',
            '/ul'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testMaskLinkWithoutProtocol()
    {
        $input = '[url=thetempe.st/station]purge[/url]';
        $expected = [
            'a' => [
                'href' => 'http://thetempe.st/station',
                'rel' => 'external',
                'target' => '_blank'
            ],
            'purge',
            '/a'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testParserEngineCaching()
    {
        $input = '[img=]foo.png[/img]';
        $result = $this->_Parser->parse($input, ['multimedia' => true]);
        $this->assertContains('<img src', $result);
        $result = $this->_Parser->parse($input, ['multimedia' => false]);
        $this->assertNotContains('<img src', $result);
    }

    public function testLink()
    {
        $input = '[url=http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&item=250678480561&ssPageName=ADME:X:RTQ:DE:1123]test[/url]';
        $expected = "<a href='http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&amp;item=250678480561&amp;ssPageName=ADME:X:RTQ:DE:1123' rel='external' target='_blank'>test</a> <span class='richtext-linkInfo'>[ebay.de]</span>";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        /*
         * external server
         */
        $input = '[url]http://heise.de/foobar[/url]';
        $expected = [
            'a' => [
                'href' => 'http://heise.de/foobar',
                'rel' => 'external',
                'target' => '_blank'
            ],
            'http://heise.de/foobar',
            '/a'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        $input = '[link]http://heise.de/foobar[/link]';
        $expected = [
            'a' => [
                'href' => 'http://heise.de/foobar',
                'rel' => 'external',
                'target' => '_blank'
            ],
            'http://heise.de/foobar',
            '/a'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // masked link
        $input = '[url=http://heise.de/foobar]foobar[/url]';
        $expected = [
            'a' => [
                'href' => 'http://heise.de/foobar',
                'rel' => 'external',
                'target' => '_blank'
            ],
            'foobar',
            '/a',
            'span' => ['class' => 'richtext-linkInfo'],
            '[heise.de]',
            '/span'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // masked link with no label
        $input = '[url=http://heise.de/foobar  label=none ]foobar[/url]';
        $expected = [
            'a' => [
                'href' => 'http://heise.de/foobar',
                'rel' => 'external',
                'target' => '_blank',
            ],
            'foobar',
            '/a',
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        /*
         * local server
         */
        $input = '[url=http://macnemo.de/foobar]foobar[/url]';
        $expected = "<a href='http://macnemo.de/foobar'>foobar</a>";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        $input = '[url]/foobar[/url]';
        $expected = [
            'a' => [
                'href' => '/foobar',
            ],
            'preg:/\/foobar/',
            '/a',
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // test lokaler server with absolute url
        $input = '[url=/foobar]foobar[/url]';
        $expected = "<a href='/foobar'>foobar</a>";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        // test 'http://' only
        $input = '[url=http://]foobar[/url]';
        $expected = "<a href='http://'>foobar</a>";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        // test for co.uk
        $input = '[url=http://heise.co.uk/foobar]foobar[/url]';
        $expected = [
            'a' => [
                'href' => 'http://heise.co.uk/foobar',
                'rel' => 'external',
                'target' => '_blank'
            ],
            'foobar',
            '/a',
            'span' => ['class' => 'richtext-linkInfo'],
            '[heise.co.uk]',
            '/span'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testHashLinkSuccess()
    {
        // inline content ([i])
        $input = "[i]#2234[/i]";
        $expected = [
            'em' => [],
            'a' => [
                'href' => '/hash/2234'
            ],
            '#2234',
            '/a',
            '/em'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // lists
        $input = "[list][*]#2234[/list]";
        $expected = [
            'ul' => true,
            'li' => true,
            'a' => [
                'href' => '/hash/2234'
            ],
            '#2234',
            '/a',
            '/li',
            '/ul'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testHashLinkFailure()
    {
        // don't hash code
        $input = '[code]#2234[/code]';
        $result = $this->_Parser->parse($input);
        $this->assertNotContains('>#2234</a>', $result);

        // not a valid hash
        $input = '#2234t';
        $result = $this->_Parser->parse($input);
        $this->assertEquals('#2234t', $result);
    }

    public function testAtLinkKnownUsers()
    {
        $input = '@Alice @Bob @Bobby Junior @Bobby Tables @Dr. No';
        $expected =
            "<a href='/at/Alice'>@Alice</a>" .
            " @Bob " .
            "<a href='/at/Bobby+Junior'>@Bobby Junior</a>" .
            " @Bobby Tables " .
            "<a href='/at/Dr.+No'>@Dr. No</a>";

        $result = $this->_Parser->parse($input);
        $this->assertEquals(
            $expected,
            $result,
            '@User string is not replaced with link to user profile.'
        );

        $input = '[code]@Alice[/code]';
        $result = $this->_Parser->parse($input);
        $this->assertNotContains('>@Alice</a>', $result);
    }

    public function testAtLinkKnownUsersLinebreak()
    {
        $input = "@Alice\nfoo";
        $result = $this->_Parser->parse($input);
        $expected = [
            'a' => ['href' => '/at/Alice'],
            '@Alice',
            '/a',
            'br' => true
        ];
        $this->assertHtml($expected, $result);
    }

    public function testLinkEmptyUrl()
    {
        $input = '[url=][/url]';
        $expected = "<a href=''></a>";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);
    }

    public function testEditMarker()
    {
        $input = 'pre [e] post';
        $expected = [
            'pre ',
            'span' => [
                'class' => 'richtext-editMark'
            ],
            '/span',
            ' post'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    /*
     * without obfuscator
     *
    public function testEmail() {
        /*
            // mailto:
            $input = '[email]mailto:mail@tosomeone.com[/email]';
            $expected = "<a href='mailto:mail@tosomeone.com'>mailto:mail@tosomeone.com</a>";
            $result = $this->Bbcode->parse($input);
            $this->assertEquals($expected, $result);

            // mailto: mask
            $input = '[email=mailto:mail@tosomeone.com]Mail[/email]';
            $expected = "<a href='mailto:mail@tosomeone.com'>Mail</a>";
            $result = $this->Bbcode->parse($input);
            $this->assertEquals($expected, $result);

            // no mailto:
            $input = '[email]mail@tosomeone.com[/email]';
            $expected = "<a href='mailto:mail@tosomeone.com'>mail@tosomeone.com</a>";
            $result = $this->Bbcode->parse($input);
            $this->assertEquals($expected, $result);

            // no mailto: mask
            $input = '[email=mail@tosomeone.com]Mail[/email]';
            $expected = "<a href='mailto:mail@tosomeone.com'>Mail</a>";
            $result = $this->Bbcode->parse($input);
            $this->assertEquals($expected, $result);
    }
            */

    public function testEmailMailto()
    {
        $MO = $this->getMockBuilder('MailObfuscator')
            ->setMethods(['link'])
            ->getMock();
        $MO->expects($this->once(4))
            ->method('link')
            ->with('mail@tosomeone.com', null);
        $this->_Helper->MailObfuscator = $MO;

        $input = '[email]mailto:mail@tosomeone.com[/email]';
        $this->_Parser->parse($input);
    }

    public function testEmailMailtoMask()
    {
        $MO = $this->getMockBuilder('MailObfuscator')
            ->setMethods(['link'])
            ->getMock();
        $MO->expects($this->once(4))
            ->method('link')
            ->with('mail@tosomeone.com', 'Mail');
        $this->_Helper->MailObfuscator = $MO;

        $input = '[email=mailto:mail@tosomeone.com]Mail[/email]';
        $this->_Parser->parse($input);
    }

    public function testEmailNoMailto()
    {
        $MO = $this->getMockBuilder('MailObfuscator')
            ->setMethods(['link'])
            ->getMock();
        $MO->expects($this->once(4))
            ->method('link')
            ->with('mail@tosomeone.com', null);
        $this->_Helper->MailObfuscator = $MO;

        $input = '[email]mail@tosomeone.com[/email]';
        $this->_Parser->parse($input);
    }

    public function testEmailNoMailtoMask()
    {
        $MO = $this->getMockBuilder('MailObfuscator')
            ->setMethods(['link'])
            ->getMock();
        $MO->expects($this->once(4))
            ->method('link')
            ->with('mail@tosomeone.com', 'Mail');
        $this->_Helper->MailObfuscator = $MO;

        $input = '[email=mail@tosomeone.com]Mail[/email]';
        $this->_Parser->parse($input);
    }

    public function testFlash()
    {
        $bbcode = '[flash_video]//www.youtube.com/v/MrBRPYlrGF8?version=3&amp;hl=en_US|560|315[/flash_video]';
        $expected = <<<EOF
			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="560" height="315">
									<param name="movie" value="//www.youtube.com/v/MrBRPYlrGF8?version=3&amp;amp;hl=en_US"></param>
									<embed src="//www.youtube.com/v/MrBRPYlrGF8?version=3&amp;amp;hl=en_US" width="560" height="315" type="application/x-shockwave-flash" wmode="opaque" style="width:560px; height:315px;" id="VideoPlayback" flashvars=""> </embed> </object>
EOF;
        $actual = $this->_Parser->parse(
            $bbcode,
            ['video_domains_allowed' => 'youtube']
        );
        $this->assertEquals(trim($expected), trim($actual));
    }

    public function testFloat()
    {
        $expected = [
            ['div' => ['class' => 'clearfix']],
            '/div',
            ['div' => ['class' => 'richtext-float']],
            'text',
            '/div',
            'more'
        ];

        $input = '[float]text[/float]more';
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testLinkAuto()
    {
        $input = 'http://heise.de/foobar';
        $expected = "<a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a>";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        // autolink surrounded by text
        $input = 'some http://heise.de/foobar text';
        $expected = "some <a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a> text";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        // no autolink in [code]
        $input = '[code]http://heise.de/foobar[/code]';
        $needle = 'heise.de/foobar</a>';
        $result = $this->_Parser->parse($input);
        $this->assertNotContains($result, $needle);

        // no autolink in [url]
        $input = '[url=http://a.com/]http://b.de/[/url]';
        $result = $this->_Parser->parse($input);
        $this->assertRegExp(
            '#href=["\']http://a.com/["\'][^>]*?>http://b.de/#',
            $result
        );

        // email autolink
        $input = 'text mail@tosomeone.com test';
        // $expected = "text <a href='mailto:mail@tosomeone.com'>mail@tosomeone.com</a> test";
        $result = $this->_Parser->parse($input);
        // $this->assertEquals($expected, $result);
        // @bogus weak test
        $this->assertRegExp('/^text .*href=".* test$/sm', $result);

        //# in list
        $input = <<<EOF
[list]
[*] http://heise.de
[/list]
EOF;
        $result = $this->_Parser->parse($input);
        $expected = "<a href='http://heise.de";
        $this->assertTextContains($expected, $result);
    }

    public function testLinkAutoWithoutHttpPrefix()
    {
        $input = 'some www.example.com/foobar text';
        $expected = [
            'some ',
            'a' => [
                'href' => 'http://www.example.com/foobar',
                'rel' => 'external',
                'target' => '_blank',
            ],
            'http://www.example.com/foobar',
            '/a',
            ' text'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testLinkAutoUrlWithinParentheses()
    {
        $input = 'some (www.example.com/foobar) text';
        $expected = [
            'some (',
            'a' => [
                'href' => 'http://www.example.com/foobar',
                'rel' => 'external',
                'target' => '_blank',
            ],
            'http://www.example.com/foobar',
            '/a',
            ') text'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testLinkAutoSurroundingChars()
    {
        $input = 'text http://example.com/?foo,,, text';
        $result = $this->_Parser->parse($input);
        $expected = [
            'text ',
            'a' => [
                'href' => 'http://example.com/?foo,,',
                'rel' => 'external',
                'target' => '_blank',
            ],
            'http://example.com/?foo,,',
            '/a',
            ', text'
        ];
        $this->assertHtml($expected, $result);

        // Question mark
        $input = 'question http://example.com/? Text';
        $result = $this->_Parser->parse($input);
        $expected = [
            'question ',
            'a' => [
                'href' => 'http://example.com/',
                'rel' => 'external',
                'target' => '_blank',
            ],
            'http://example.com/',
            '/a',
            '? Text'
        ];
        $this->assertHtml($expected, $result);

        // No Question mark but url
        $input = 'no question http://example.com/?foo=bar text';
        $result = $this->_Parser->parse($input);
        $expected = [
            'no question ',
            'a' => [
                'href' => 'http://example.com/?foo=bar',
                'rel' => 'external',
                'target' => '_blank',
            ],
            'http://example.com/?foo=bar',
            '/a',
            ' text'
        ];
        $this->assertHtml($expected, $result);
    }

    public function testReturnText()
    {
        $in = 'test [b]test[b] test';
        $expected = 'test test test';
        $actual = $this->_Parser->parse($in, ['return' => 'text']);
        $this->assertEquals($expected, $actual);
    }

    public function testShortenLink()
    {
        $maxLength = 15;
        $this->MarkupSettings->setSingle('text_word_maxlength', $maxLength);

        $input = '[url]http://this/url/is/32/chars/long[/url]';
        $expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";

        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        $input = 'http://this/url/is/32/chars/long';
        $expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);
    }

    public function testIframe()
    {
        //* test allowed domain
        $input = '[iframe height=349 width=560 ' .
            'src=http://www.youtube.com/embed/HdoW3t_WorU ' .
            'frameborder=0][/iframe]';
        $expected = [
            [
                'div' => [
                    'class' => 'embed-responsive embed-responsive-16by9',
                ],
                'iframe' => [
                    'class' => 'embed-responsive-item',
                    'src' => 'http://www.youtube.com/embed/HdoW3t_WorU?&amp;wmode=Opaque',
                    'height' => '349',
                    'width' => '560',
                    'frameborder' => '0'
                ]
            ],
            '/iframe',
        ];
        $result = $this->_Parser->parse(
            $input,
            ['video_domains_allowed' => 'youtube | vimeo']
        );
        $this->assertHtml($expected, $result);

        //* test forbidden domains
        $input = '[iframe height=349 width=560 ' .
            'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
            'frameborder=0][/iframe]';
        $pattern = '/src/i';
        $result = $this->_Parser->parse(
            $input,
            ['video_domains_allowed' => 'youtube | vimeo']
        );
        $this->assertNotRegExp($pattern, $result);
    }

    public function testIframeAllDomainsAllowed()
    {
        $input = '[iframe height=349 width=560 ' .
            'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
            '][/iframe]';
        $expected = 'src="http://www.youtubescam.com/embed/HdoW3t_WorU';
        $this->MarkupSettings->setSingle('video_domains_allowed', '*');
        $result = $this->_Parser->parse($input);
        $this->assertContains($expected, $result);
    }

    public function testIframeNoDomainAllowed()
    {
        $input = '[iframe height=349 width=560 ' .
            'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
            '][/iframe]';
        $expected = '/src/i';
        $result = $this->_Parser->parse(
            $input,
            ['video_domains_allowed' => '']
        );
        $this->assertNotRegExp($expected, $result);
    }

    public function testExternalImageAbsoluteAutoLinked()
    {
        // test for standard URIs
        $input = '[img]http://foo.bar/img/macnemo.png[/img]';
        $expected = [
            'a' => [
                'href' => 'http://foo.bar/img/macnemo.png',
                // 'rel' => 'external',
                'target' => '_blank',
            ],
            'img' => [
                'src' => 'http://foo.bar/img/macnemo.png',
                'alt' => ''
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testExternalImageRelativeAutoLinked()
    {
        // test for standard URIs
        $input = '[img]/somewhere/macnemo.png[/img]';
        $expected = [
            'a' => [
                'href' => '/somewhere/macnemo.png',
                'target' => '_blank',
            ],
            'img' => [
                'src' => '/somewhere/macnemo.png',
                'alt' => ''
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    /**
     * test scaling with 1 parameter
     */
    public function testExternalImageAbsoluteAutoLinkedScaledByOne()
    {
        // test for standard URIs
        $input = '[img=50]http://foo.bar/img/macnemo.png[/img]';
        $expected = [
            'a' => [
                'href' => 'http://foo.bar/img/macnemo.png',
                'target' => '_blank',
            ],
            'img' => [
                'src' => 'http://foo.bar/img/macnemo.png',
                'alt' => '',
                'width' => '50',
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    /**
     * test scaling with 2 parameter
     */
    public function testExternalImageAbsoluteAutoLinkedScaledByTwo()
    {
        // test for standard URIs
        $input = '[img=50x100]http://foo.bar/img/macnemo.png[/img]';
        $expected = [
            'a' => [
                'href' => 'http://foo.bar/img/macnemo.png',
                'target' => '_blank',
            ],
            'img' => [
                'src' => 'http://foo.bar/img/macnemo.png',
                'alt' => '',
                'height' => '100',
                'width' => '50',
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testExternalImageWithHttpsEnforced()
    {
        $_SERVER['HTTPS'] = true;
        $input = '[img=]http://foo.bar/img/macnemo.png[/img]';
        $expected = [
            'a' => [
                'href' => 'https://foo.bar/img/macnemo.png',
                'target' => '_blank',
            ],
            'img' => [
                'src' => 'https://foo.bar/img/macnemo.png',
                'alt' => '',
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
        unset($_SERVER['HTTPS']);
    }

    public function testImageNestedInExternalLink()
    {
        $input = '[url=http://heise.de][img]http://heise.de/img.png[/img][/url]';

        /*
        $expected = "<a href='http://heise.de' rel='external' target='_blank'><img src=\"http://heise.de/img.png\" class=\"external_image\" style=\"\" width=\"auto\" height=\"auto\" alt=\"\" /></a>";
        */
        $expected = [
            [
                'a' => [
                    'href' => 'http://heise.de',
                    'rel' => 'external',
                    'target' => '_blank',
                ]
            ],
            [
                'img' => [
                    'src' => 'http://heise.de/img.png',
                    'alt' => '',
                ]
            ],
            '/a'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    /**
     * [uploads]<image>[/uploads]
     */
    public function testInternalImageAutoLinked()
    {
        //// internal image
        $input = '[upload]test.png[/upload]';
        $expected = [
            [
                'a' => [
                    'href' => '/useruploads/test.png',
                    'target' => '_blank',
                ],
                'img' => [
                    'alt' => '',
                    'src' => '/useruploads/test.png',
                ],
            ],
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        //// internal image with attributes
        $input = '[upload width=50 height=60]test.png[/upload]';
        $expected = [
            [
                'a' => [
                    'href' => '/useruploads/test.png',
                    'target' => '_blank',
                ],
                'img' =>
                    [
                        'alt' => '',
                        'src' => '/useruploads/test.png',
                        'width' => '50',
                        'height' => '60',
                    ]
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // nested image does not have [domain.info]
        $input = '[url=http://heise.de][upload]test.png[/upload][/url]';
        $expected = "/richtext-linkInfo/";
        $result = $this->_Parser->parse($input);
        $this->assertNotRegExp($expected, $result);
    }

    public function testInternalImageExternallyLinked()
    {
        //// internal image
        $input = '[url=http://foo.de][upload]test.png[/upload][/url]';
        $expected = [
            [
                'a' => [
                    'href' => 'http://foo.de',
                    'rel' => 'external',
                    'target' => '_blank',
                ],
                'img' => [
                    'src' => '/useruploads/test.png',
                    'alt' => '',
                ]
            ],
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testUploadTypeImage()
    {
        //// internal image
        $input = '[img src=upload]test.png[/img]';
        $expected = [
            [
                'a' => [
                    'href' => '/useruploads/test.png',
                    'target' => '_blank',
                ],
                'img' => [
                    'src' => '/useruploads/test.png',
                    'alt' => '',
                ]
            ],
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testUploadTypeAudio()
    {
        //// internal image
        $input = '[audio src=upload]test.mp3[/audio]';
        $expected = [
            'audio' => [
                'controls' => 'controls',
                'preload' => 'auto',
                'src' => '/useruploads/test.mp3',
                'x-webkit-airplay' => 'allow',
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testUploadTypeVideo()
    {
        $input = '[video src=upload]test.mp4[/video]';
        $expected = [
            'video' => [
                'controls' => 'controls',
                'preload' => 'auto',
                'src' => '/useruploads/test.mp4',
                'x-webkit-airplay' => 'allow',
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testUploadTypeFile()
    {
        $input = '[file src=upload]test.txt[/file]';
        $expected = [
            'a' => [
                'href' => '/useruploads/test.txt',
                'target' => '_blank',
            ],
            'test.txt',
            '/a'
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testUploadTypeFileSrcNotValid()
    {
        $input = '[file src=foo]test.txt[/file]';
        $expected = [
            'div' => [
                'class' => 'richtext-imessage',
            ],
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testUploadTypeFileNoSource()
    {
        $input = '[file]test.txt[/file]';
        $result = $this->_Parser->parse($input);
        $this->assertHtml($input, $result);
    }

    public function testSmiliesNoSmiliesInCodeTag()
    {
        $input = '[code text]:)[/code]';
        $needle = '<img';
        $result = $this->_Parser->parse($input, ['cache' => false]);
        $this->assertNotContains($needle, $result);
    }

    public function testCodeNestedTags()
    {
        $input = '[code][b]text[b][/code]';
        $expected = [
            [
                'div' => ['class' => 'geshi-wrapper']
            ],
            'preg:/.*?\[b\]text\[b\].*/',
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);
    }

    public function testCodeWhitespace()
    {
        $input = "[code]\ntest\n[/code]";
        $expected = "/>test</";
        $result = $this->_Parser->parse($input);
        $this->assertRegExp($expected, $result);
    }

    public function testCodeSimple()
    {
        $input = '[code]text[/code]';
        $result = $this->_Parser->parse($input);
        $expected = 'lang="text"';
        $this->assertContains($expected, $result);
    }

    public function testCodeLangAttribute()
    {
        $input = '[code=php]text[/code]';
        $result = $this->_Parser->parse($input);
        $expected = 'lang="php"';
        $this->assertContains($expected, $result);
    }

    /**
     * tests that citation marks are not replaced in code-blocks
     */
    public function testCodeNoCitationMark()
    {
        // [code]<citation mark>[/code] should not be cited
        $input = h(
            "[code]\n" . $this->_Helper->getConfig('quote_symbol') . "\n[/code]"
        );
        $expected = '`span class=.*?richtext-citation`';
        $result = $this->_Parser->parse($input);
        $this->assertNotRegExp($expected, $result);
    }

    public function testCodeDetaginize()
    {
        $input = '[code bash]pre http://example.com post[/code]';
        $result = $this->_Parser->parse($input);
        $this->assertNotContains('autoLink', $result);
    }

    public function testQuote()
    {
        $_qs = $this->MarkupSettings->get('quote_symbol');
        $input = $_qs . ' fo [b]test[/b] ba';
        $result = $this->_Parser->parse($input);
        $expected = [
            'span' => ['class' => 'richtext-citation'],
            $_qs . ' fo ',
            'strong' => [],
            'test',
            '/strong',
            ' ba',
            '/span'
        ];
        $this->assertHtml($expected, $result);
    }

    public function testHtml5Video()
    {
        //* setup
        $bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
        Configure::write('Saito.Settings.bbcode_img', true);

        //* @td write video tests
        $url = 'http://example.com/audio.mp4';
        $input = "[video]{$url}[/video]";
        $result = $this->_Parser->parse($input);
        $expected = [
            'video' => [
                'src' => $url,
                'preload' => 'auto',
                'controls' => 'controls',
                'x-webkit-airplay' => 'allow'
            ],
        ];
        $this->assertHtml($expected, $result);

        //* teardown
        Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
    }

    public function testHr()
    {
        $input = '[hr][hr]';
        $expected = '<hr><hr>';
        $result = $this->_Parser->parse($input);
        $this->assertEquals($result, $expected);
    }

    public function testHrShort()
    {
        $input = '[---][---]';
        $expected = '<hr><hr>';
        $result = $this->_Parser->parse($input);
        $this->assertEquals($result, $expected);
    }

    public function testEmbedNoReplacement()
    {
        $input = '[embed]http://no.provider/unreplaced[/embed]';

        $result = $this->_Parser->parse($input);

        $expected = [
            'div' => [
                'class' => 'js-embed',
                'data-embed' => '{&quot;url&quot;:&quot;http:\/\/no.provider\/unreplaced&quot;}',
                'id' => 'embed-10478631dd9f8f00da95953f63f6e5f3',
            ],
            '/div',
        ];

        $this->assertHtml($expected, $result);
    }

    public function testEmbedDisabledWithoutAutolinking()
    {
        $this->MarkupSettings->setSingle('autolink', false);
        $this->MarkupSettings->setSingle('content_embed_active', false);

        $url = 'http://foo.bar/baz';
        $input = "[embed]{$url}[/embed]";

        $result = $this->_Parser->parse($input);

        $this->assertHtml($url, $result);
    }

    public function testEmbedDisabledWithAutolinking()
    {
        $this->MarkupSettings->setSingle('autolink', true);
        $this->MarkupSettings->setSingle('content_embed_active', false);

        $url = 'http://foo.bar/baz';
        $input = "[embed]{$url}[/embed]";

        $result = $this->_Parser->parse($input);

        $expected = [
            'a' => [
                'href' => $url,
                'target' => '_blank',
            ],
            $url,
            '/a',
        ];

        $this->assertHtml($expected, $result);
    }

    public function testHtml5Audio()
    {
        //* setup
        $bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
        Configure::write('Saito.Settings.bbcode_img', true);

        //* simple test
        $url = 'http://example.com/audio3.m4a';
        $input = "[audio]{$url}[/audio]";
        $result = $this->_Parser->parse($input);
        $expected = [
            'audio' => [
                'src' => $url,
                'controls' => 'controls',
                'preload' => 'auto',
                'x-webkit-airplay' => 'allow',
            ],
            '/audio',
        ];
        $this->assertHtml($expected, $result);

        //* teardown
        Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
    }

    /* ******************** Setup ********************** */

    public function setUp()
    {
        Cache::clear();

        if (Cache::getConfig('bbcodeParserEmbed') === null) {
            Cache::setConfig(
                'bbcodeParserEmbed',
                [
                    'className' => 'File',
                    'prefix' => 'saito_embed-',
                    'path' => CACHE,
                    'groups' => ['embed'],
                    'duration' => '+1 year'

                ]
            );
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            $this->server_name = $_SERVER['SERVER_NAME'];
        } else {
            $this->server_name = false;
        }

        if (isset($_SERVER['SERVER_PORT'])) {
            $this->server_port = $_SERVER['SERVER_PORT'];
        } else {
            $this->server_port = false;
        }

        $this->autolink = Configure::read('Saito.Settings.autolink');
        Configure::write('Saito.Settings.autolink', true);

        $_SERVER['SERVER_NAME'] = 'macnemo.de';
        $_SERVER['SERVER_PORT'] = '80';

        parent::setUp();

        $View = new View();

        //= smiley fixture
        $smiliesFixture = [
            [
                'order' => 1,
                'icon' => 'wink.png',
                'image' => 'wink.png',
                'title' => 'Wink',
                'code' => ';)',
                'type' => 'image'
            ],
            [
                'order' => 2,
                'icon' => 'smile_icon.svg',
                'image' => 'smile_image.svg',
                'title' => 'Smile',
                'code' => ':-)',
                'type' => 'image'
            ],
            [
                'order' => 3,
                'icon' => 'coffee',
                'image' => 'coffee',
                'title' => 'Coffee',
                'code' => '[_]P',
                'type' => 'font'
            ],
        ];
        Cache::write('Saito.Smilies.data', $smiliesFixture);
        $SmileyLoader = new \Saito\Smiley\SmileyLoader();

        //= userlist fixture
        $Userlist = $this->getMockBuilder(UserlistModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $Userlist->method('get')->willReturn(['Alice', 'Bobby Junior', 'Dr. No']);

        //= ParserHelper
        $markupSettingsMock = new class extends MarkupSettings {
            public function setSingle(string $key, $value)
            {
                $this->_settings[$key] = $value;
            }
        };
        $this->MarkupSettings = new $markupSettingsMock([
            'UserList' => $Userlist,
            'atBaseUrl' => '/at/',
            'autolink' => true,
            'bbcode_img' => true,
            'content_embed_active' => true,
            'content_embed_media' => true,
            'content_embed_text' => true,
            'hashBaseUrl' => '/hash/',
            'quote_symbol' => '»',
            'smilies' => true,
            'smiliesData' => $SmileyLoader,
            'text_word_maxlength' => 100000,
            'video_domains_allowed' => 'youtube',
            'webroot' => ''
        ]);
        $this->_Helper = $ParserHelper = new ParserHelper($View);
        $ParserHelper->beforeRender(null);

        //= Smiley Renderer
        $this->_Helper->getView()->set('smiliesData', $SmileyLoader);

        //= Parser
        $this->_Parser = new Parser($ParserHelper, $this->MarkupSettings);
    }

    public function tearDown()
    {
        parent::tearDown();
        if ($this->server_name) {
            $_SERVER['SERVER_NAME'] = $this->server_name;
        }

        if ($this->server_name) {
            $_SERVER['SERVER_PORT'] = $this->server_port;
        }

        Configure::write('Saito.Settings.autolink', $this->autolink);

        Cache::clear();
        unset($this->_Parser);
    }
}
