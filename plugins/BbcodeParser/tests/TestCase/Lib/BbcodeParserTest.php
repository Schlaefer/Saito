<?php

namespace BbcodeParser\Test\Lib;

use App\View\Helper\ParserHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\View\View;
use Plugin\BbcodeParser\Lib;
use Plugin\BbcodeParser\src\Lib\Parser;
use Saito\Test\SaitoTestCase;
use Saito\User\Userlist;

class BbcodeParserTest extends SaitoTestCase
{

    protected $_Parser = null;

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

        $input = '[url]http://this/url/is/32/chars/long[/url]';
        $expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
        $result = $this->_Parser->parse(
            $input,
            ['text_word_maxlength' => $maxLength]
        );
        $this->assertEquals($expected, $result);

        $input = 'http://this/url/is/32/chars/long';
        $expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
        $result = $this->_Parser->parse(
            $input,
            ['text_word_maxlength' => $maxLength]
        );
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
                'iframe' => [
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
        $result = $this->_Parser->parse(
            $input,
            ['video_domains_allowed' => '*']
        );
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

    public function testExternalImage()
    {
        $bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
        Configure::write('Saito.Settings.bbcode_img', true);

        // test for standard URIs
        $input = '[img]http://localhost/img/macnemo.png[/img]';
        $expected = [
            'img' => [
                'src' => 'http://localhost/img/macnemo.png',
                'alt' => ''
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // test for URIs without protocol
        $input = '[img]/somewhere/macnemo.png[/img]';
        $expected = '<img src="' . $this->_Helper->webroot . '/somewhere/macnemo.png" alt=""/>';
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        // test scaling with 1 parameter
        $input = '[img=50]http://localhost/img/macnemo.png[/img]';
        $expected = '<img src="http://localhost/img/macnemo.png" width="50" alt=""/>';
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        // test scaling with 2 parameters
        $input = '[img=50x100]http://localhost/img/macnemo.png[/img]';
        $expected = '<img src="http://localhost/img/macnemo.png" width="50" height="100" alt=""/>';
        $result = $this->_Parser->parse($input);
        $this->assertEquals($expected, $result);

        // float left
        $input = '[img=left]http://localhost/img/macnemo.png[/img]';
        $expected = [
            [
                'img' => [
                    'src' => 'http://localhost/img/macnemo.png',
                    'style' => "float: left;",
                    'alt' => "",
                ]
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // float right
        $input = '[img=right]http://localhost/img/macnemo.png[/img]';
        $expected = [
            [
                'img' => [
                    'src' => 'http://localhost/img/macnemo.png',
                    'style' => "float: right;",
                    'alt' => "",
                ]
            ]
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
    }

    public function testImageNestedInExternalLink()
    {
        $bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
        Configure::write('Saito.Settings.bbcode_img', true);

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

        Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
    }

    public function testInternalImage()
    {
        $this->markTestSkipped('Image uploader not implemented.');
        // Create a map of arguments to return values.
        $map = [
            ['test.png', [], '<img src="test.png" />'],
            [
                'test.png',
                [
                    'autoResize' => false,
                    'resizeThumbOnly' => false,
                    'width' => '50',
                    'height' => '60',
                ],
                '<img src="test.png" width="50" height="60" alt="">'
            ]
        ];
        $FileUploader = $this->getMockBuilder('FileUploaderHelper')
            ->setMethods(['image', 'reset'])
            ->getMock();
        $FileUploader->expects($this->atLeastOnce())
            ->method('image')
            ->will($this->returnValueMap($map));
        $this->_Helper->FileUpload = $FileUploader;

        // internal image
        $input = '[upload]test.png[/upload]';
        $expected = [
            ['img' => ['src' => 'test.png']],
        ];
        $result = $this->_Parser->parse($input);
        $this->assertHtml($expected, $result);

        // internal image with attributes
        $input = '[upload width=50 height=60]test.png[/upload]';
        $expected = [
            [
                'img' =>
                    [
                        'src' => 'test.png',
                        'width' => '50',
                        'height' => '60',
                        'alt' => ''
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
            "[code]\n" . $this->_Helper->config('quote_symbol') . "\n[/code]"
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
        $_qs = h($this->_Helper->config('quote_symbol'));
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
                'controls' => 'controls',
                'x-webkit-airplay' => 'allow'
            ],
        ];
        $this->assertHtml($expected, $result);

        //* test autoconversion for audio files
        $html5AudioExtensions = ['m4a', 'ogg', 'mp3', 'wav', 'opus'];
        foreach ($html5AudioExtensions as $extension) {
            $url = 'http://example.com/audio.' . $extension;
            $input = "[video]{$url}[/video]";
            $result = $this->_Parser->parse($input);
            $expected = [
                'audio' => ['src' => $url, 'controls' => 'controls'],
            ];
            $this->assertHtml($expected, $result);
        }

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

    public function testEmbedlyDisabled()
    {
        $observer = $this->getMockBuilder('Embedly')
            ->setMethods(['setApiKey', 'embedly'])
            ->getMock();
        $observer->expects($this->never())
            ->method('setApiKey');
        $this->_Helper->Embedly = $observer;
        $input = '[embed]foo[/embed]';
        $this->_Parser->parse($input);
    }

    public function testEmbedlyEnabled()
    {
        $observer = $this->getMockBuilder('Embedly')
            ->setMethods(['setApiKey', 'embedly'])
            ->getMock();
        $observer->expects($this->once())
            ->method('setApiKey')
            ->with($this->equalTo('abc123'));
        $observer->expects($this->once())
            ->method('embedly')
            ->with($this->equalTo('foo'));
        $this->_Helper->Embedly = $observer;
        $input = '[embed]foo[/embed]';

        $this->_Parser->parse(
            $input,
            [
                'embedly_enabled' => true,
                'embedly_key' => 'abc123'
            ]
        );
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
            'audio' => ['src' => $url, 'controls' => 'controls'],
        ];
        $this->assertHtml($expected, $result);

        //* teardown
        Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
    }

    /* ******************** Setup ********************** */

    public function setUp()
    {
        Cache::clear();

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

        $this->text_word_maxlength = Configure::read(
            'Saito.Settings.text_word_maxlength'
        );
        Configure::write('Saito.Settings.text_word_maxlength', 10000);

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
        $Userlist = new Userlist\UserlistArray();
        $Userlist->set(['Alice', 'Bobby Junior', 'Dr. No']);

        //= ParserHelper
        $settings = [
            'autolink' => true,
            'bbcode_img' => true,
            'multimedia' => true,
            'quote_symbol' => '»',
            'hashBaseUrl' => '/hash/',
            'atBaseUrl' => '/at/',
            'return' => 'html',
            'smilies' => true,
            'smiliesData' => $SmileyLoader,
            'text_word_maxlength' => 120,
            'UserList' => $Userlist,
            'webroot' => ''
        ];
        $this->_Helper = $ParserHelper = new ParserHelper($View, $settings);
        $ParserHelper->beforeRender(null);

        //= Smiley Renderer
        $SmileyRenderer = new \Saito\Smiley\SmileyRenderer($SmileyLoader);
        $SmileyRenderer->setHelper($this->_Helper);
        $this->_Helper->SmileyRenderer = $SmileyRenderer;

        //= Parser
        $this->_Parser = new Parser($ParserHelper, $settings);
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

        Configure::write(
            'Saito.Settings.text_word_maxlength',
            $this->text_word_maxlength
        );

        Configure::write('Saito.Settings.autolink', $this->autolink);

        Cache::clear();
        unset($this->_Parser);
    }
}
