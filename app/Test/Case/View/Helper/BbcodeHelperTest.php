<?php

	/* Bbcode Test cases generated on: 2010-08-02 07:08:44 : 1280727824 */
	App::import('Lib', 'Stopwatch.Stopwatch');
	App::import('Helper',
		array(
			'FileUpload.FileUpload',
			'CakephpGeshi.Geshi',
		));
	App::import('Helper', 'MailObfuscator.MailObfuscator');

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('BbcodeHelper', 'View/Helper');
	App::uses('HtmlHelper', 'View/Helper');
	App::uses('CakeRequest', 'Network');

	App::uses('BbcodeUserlistArray', 'Lib/Bbcode');

	class BbcodeHelperTest extends CakeTestCase {

		protected $_Bbcode = null;

		public function testBold() {
			$input = '[b]bold[/b]';
			$expected = array('strong' => array(), 'bold', '/strong');
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testEmphasis() {
			$input = '[i]italic[/i]';
			$expected = array('em' => array(), 'italic', '/em');
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testUnderline() {
			$input = '[u]text[/u]';
			$expected = array('span' => array('class' => 'c-bbcode-underline'),
				'text', '/span');
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testStrike() {
			$expected = ['del' => [], 'text', '/del'];

			// [strike]
			$input = '[strike]text[/strike]';
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// [s]
			$input = '[s]text[/s]';
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testSpoiler() {
			$input = 'pre [spoiler] te "\' xt[/spoiler]';
			$expected = [
				'pre',
				[
					'div' => [
						'class' => 'c-bbcode-spoiler',
						'style' => 'display: inline;'
					]
				],
				['script' => true],
				'preg:/(.*?)"string":" te &quot;&#039; xt"(.*?)(?=<)/',
				'/script',
				[
					'a' => [
						'href' => '#',
						'class' => 'c-bbcode-spoiler-link',
						'onclick'
					]
				],
				'preg:/.*▇ Spoiler ▇.*?(?=<)/',
				'/a',
				'/div'
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected, true);
		}

		public function testList() {
			$input = "[list]\n[*]fooo\n[*]bar\n[/list]";
			$expected = [
				['ul' => ['class' => 'c-bbcode-ul']],
				['li' => ['class' => 'c-bbcode-li']],
				'fooo',
				['br' => []],
				'/li',
				['li' => ['class' => 'c-bbcode-li']],
				'bar',
				['br' => []],
				'/li',
				'/ul'
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testMaskLinkWithoutProtocol() {
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
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testLink() {
			$input = '[url=http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&item=250678480561&ssPageName=ADME:X:RTQ:DE:1123]test[/url]';
			$expected = "<a href='http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&amp;item=250678480561&amp;ssPageName=ADME:X:RTQ:DE:1123' rel='external' target='_blank'>test</a> <span class='c-bbcode-link-dinfo'>[ebay.de]</span>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			/**
			 * external server
			 */
			$input = '[url]http://heise.de/foobar[/url]';
			$expected = array(
				'a' => array(
					'href' => 'http://heise.de/foobar',
					'rel' => 'external',
					'target' => '_blank'
				),
				'http://heise.de/foobar',
				'/a'
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			$input = '[link]http://heise.de/foobar[/link]';
			$expected = array(
				'a' => array(
					'href' => 'http://heise.de/foobar',
					'rel' => 'external',
					'target' => '_blank'
				),
				'http://heise.de/foobar',
				'/a'
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// masked link
			$input = '[url=http://heise.de/foobar]foobar[/url]';
			$expected = array(
				'a' => array(
					'href' => 'http://heise.de/foobar',
					'rel' => 'external',
					'target' => '_blank'
				),
				'foobar',
				'/a',
				'span' => array('class' => 'c-bbcode-link-dinfo'), '[heise.de]', '/span'
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// masked link with no label
			$input = '[url=http://heise.de/foobar  label=none ]foobar[/url]';
			$expected = array(
				'a' => array(
					'href' => 'http://heise.de/foobar',
					'rel' => 'external',
					'target' => '_blank',
				),
				'foobar',
				'/a',
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			/**
			 * local server
			 */
			$input = '[url=http://macnemo.de/foobar]foobar[/url]';
			$expected = "<a href='http://macnemo.de/foobar'>foobar</a>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			$input = '[url]/foobar[/url]';
			$expected = array(
				'a' => array(
					'href' => '/foobar',
				),
				'preg:/\/foobar/',
				'/a',
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// test lokaler server with absolute url
			$input = '[url=/foobar]foobar[/url]';
			$expected = "<a href='/foobar'>foobar</a>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// test 'http://' only
			$input = '[url=http://]foobar[/url]';
			$expected = "<a href='http://'>foobar</a>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// test for co.uk
			$input = '[url=http://heise.co.uk/foobar]foobar[/url]';
			$expected = array(
				'a' => array(
					'href' => 'http://heise.co.uk/foobar',
					'rel' => 'external',
					'target' => '_blank'
				),
				'foobar',
				'/a',
				'span' => array('class' => 'c-bbcode-link-dinfo'), '[heise.co.uk]',
				'/span'
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testHashLinkSuccess() {
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
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// lists
			$input = "[list][*]#2234[/list]";
			$expected = [
				'ul' => ['class'],
				'li' => ['class'],
				'a' => [
					'href' => '/hash/2234'
				],
				'#2234',
				'/a',
				'/li',
				'/ul'
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testHashLinkFailure() {
			// don't hash code
			$input = '[code]#2234[/code]';
			$result = $this->_Bbcode->parse($input);
			$this->assertNotContains('>#2234</a>', $result);

			// not a valid hash
			$input = '#2234t';
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals('#2234t', $result);
		}

		public function testAtLinkKnownUsers() {
			$input = '@Alice @Bob @Bobby Junior @Bobby Tables @Dr. No';
			$expected =
				"<a href='/at/Alice'>@Alice</a>" .
				" @Bob " .
				"<a href='/at/Bobby+Junior'>@Bobby Junior</a>" .
				" @Bobby Tables " .
				"<a href='/at/Dr.+No'>@Dr. No</a>";

			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result, '@User string is not replaced with link to user profile.');

			$input = '[code]@Alice[/code]';
			$result = $this->_Bbcode->parse($input);
			$this->assertNotContains('>@Alice</a>', $result);
		}

		public function testLinkEmptyUrl() {
			$input = '[url=][/url]';
			$expected = "<a href=''></a>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);
		}

		public function testEditMarker() {
			$input = 'pre [e] post';
			$expected = [
				'pre ',
				'span' => [
					'class' => 'c-bbcode-edit'
				],
				'/span',
				' post'
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
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

		public function testEmailMailto() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
				->method('link')
				->with('mail@tosomeone.com', null);
			$this->_Bbcode->MailObfuscator = $MO;

			$input = '[email]mailto:mail@tosomeone.com[/email]';
			$this->_Bbcode->parse($input);
		}

		public function testEmailMailtoMask() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
				->method('link')
				->with('mail@tosomeone.com', 'Mail');
			$this->_Bbcode->MailObfuscator = $MO;

			$input = '[email=mailto:mail@tosomeone.com]Mail[/email]';
			$this->_Bbcode->parse($input);
		}

		public function testEmailNoMailto() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
				->method('link')
				->with('mail@tosomeone.com', null);
			$this->_Bbcode->MailObfuscator = $MO;

			$input = '[email]mail@tosomeone.com[/email]';
			$this->_Bbcode->parse($input);
		}

		public function testEmailNoMailtoMask() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
				->method('link')
				->with('mail@tosomeone.com', 'Mail');
			$this->_Bbcode->MailObfuscator = $MO;

			$input = '[email=mail@tosomeone.com]Mail[/email]';
			$this->_Bbcode->parse($input);
		}

		public function testFlash() {
			Configure::write('Saito.Settings.bbcode_img', true);
			Configure::write('Saito.Settings.video_domains_allowed', 'youtube');

			$bbcode = '[flash_video]//www.youtube.com/v/MrBRPYlrGF8?version=3&amp;hl=en_US|560|315[/flash_video]';
			$expected = <<<EOF
			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="560" height="315">
									<param name="movie" value="//www.youtube.com/v/MrBRPYlrGF8?version=3&amp;amp;hl=en_US"></param>
									<embed src="//www.youtube.com/v/MrBRPYlrGF8?version=3&amp;amp;hl=en_US" width="560" height="315" type="application/x-shockwave-flash" wmode="opaque" style="width:560px; height:315px;" id="VideoPlayback" flashvars=""> </embed> </object>
EOF;
			$actual = $this->_Bbcode->parse($bbcode);
			$this->assertEquals(trim($expected), trim($actual));
		}

		public function testFloat() {
			$expected = [
				'div' => ['class' => 'c-bbcode-float'],
				'text',
				'/div',
				'more'
			];

			$input = '[float]text[/float]more';
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testLinkAuto() {
			$input = 'http://heise.de/foobar';
			$expected = "<a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// autolink surrounded by text
			$input = 'some http://heise.de/foobar text';
			$expected = "some <a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a> text";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// no autolink in [code]
			$input = '[code]http://heise.de/foobar[/code]';
			$needle = 'heise.de/foobar</a>';
			$result = $this->_Bbcode->parse($input);
			$this->assertNotContains($result, $needle);

			// no autolink in [url]
			$input = '[url=http://a.com/]http://b.de/[/url]';
			$result = $this->_Bbcode->parse($input);
			$this->assertRegExp('#href=["\']http://a.com/["\'][^>]*?>http://b.de/#', $result);

			// email autolink
			$input = 'text mail@tosomeone.com test';
			// $expected = "text <a href='mailto:mail@tosomeone.com'>mail@tosomeone.com</a> test";
			$result = $this->_Bbcode->parse($input);
			// $this->assertEquals($expected, $result);
			// @bogus weak test
			$this->assertRegExp('/^text .*href=".* test$/sm', $result);

			//# in list
			$input = <<<EOF
[list]
[*] http://heise.de
[/list]
EOF;
			$result = $this->_Bbcode->parse($input);
			$expected = "<a href='http://heise.de";
			$this->assertTextContains($expected, $result);
		}

		public function testLinkAutoWithoutHttpPrefix() {
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
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testLinkAutoUrlWithinParentheses() {
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
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testLinkAutoSurroundingChars() {
			$input = 'text http://example.com/?foo,,, text';
			$result = $this->_Bbcode->parse($input);
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
			$this->assertTags($result, $expected);

			// Question mark
			$input = 'question http://example.com/? Text';
			$result = $this->_Bbcode->parse($input);
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
			$this->assertTags($result, $expected);

			// No Question mark but url
			$input = 'no question http://example.com/?foo=bar text';
			$result = $this->_Bbcode->parse($input);
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
			$this->assertTags($result, $expected);
		}

		public function testReturnText() {
			$in = 'test [b]test[b] test';
			$expected = 'test test test';
			$actual = $this->_Bbcode->parse($in, ['return' => 'text']);
			$this->assertEquals($expected, $actual);
		}

		public function testShortenLink() {
			$maxLength = 15;

			$textWordMaxLength = Configure::read('Saito.Settings.text_word_maxlength');
			Configure::write('Saito.Settings.text_word_maxlength', $maxLength);

			$input = '[url]http://this/url/is/32/chars/long[/url]';
			$expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			$input = 'http://this/url/is/32/chars/long';
			$expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			Configure::write('Saito.Settings.text_word_maxlength',
				$textWordMaxLength);
		}

		public function testIframe() {
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			$videoDomains = Configure::read('Saito.Settings.video_domains_allowed');
			Configure::write('Saito.Settings.video_domains_allowed',
				'youtube | vimeo');

			//* test allowed domain
			$input = '[iframe height=349 width=560 ' .
				'src=http://www.youtube.com/embed/HdoW3t_WorU ' .
				'frameborder=0][/iframe]';
			$expected = [
				['iframe' => [
					'src' => 'http://www.youtube.com/embed/HdoW3t_WorU?&wmode=Opaque',
					'height' => '349',
					'width' => '560',
					'frameborder' => '0']
				],
				'/iframe',
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* test forbidden domains
			$input = '[iframe height=349 width=560 ' .
				'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
				'frameborder=0][/iframe]';
			$expected = '/src/i';
			$result = $this->_Bbcode->parse($input);
			$this->assertNoPattern($expected, $result);
		}

		public function testIframeAllDomainsAllowed() {
			Configure::write('Saito.Settings.bbcode_img', true);
			Configure::write('Saito.Settings.video_domains_allowed', '*');

			$input = '[iframe height=349 width=560 ' .
				'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
				'][/iframe]';
			$expected = 'src="http://www.youtubescam.com/embed/HdoW3t_WorU';
			$result = $this->_Bbcode->parse($input);
			$this->assertContains($expected, $result);
		}

		public function testIframeNoDomainAllowed() {
			Configure::write('Saito.Settings.bbcode_img', true);
			Configure::write('Saito.Settings.video_domains_allowed', '');

			$input = '[iframe height=349 width=560 ' .
				'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
				'][/iframe]';
			$expected = '/src/i';
			$result = $this->_Bbcode->parse($input);
			$this->assertNotRegExp($expected, $result);
		}

		public function testExternalImage() {
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			// test for standard URIs
			$input = '[img]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c-bbcode-external-image" alt="" />';
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// test for URIs without protocol
			$input = '[img]/somewhere/macnemo.png[/img]';
			$expected = '<img src="' . $this->_Bbcode->webroot . 'somewhere/macnemo.png" class="c-bbcode-external-image" alt="" />';
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// test scaling with 1 parameter
			$input = '[img=50]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c-bbcode-external-image" width="50" alt="" />';
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// test scaling with 2 parameters
			$input = '[img=50x100]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c-bbcode-external-image" width="50" height="100" alt="" />';
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($expected, $result);

			// float left
			$input = '[img=left]http://localhost/img/macnemo.png[/img]';
			$expected = array(
				array('img' => array(
					'src' => 'http://localhost/img/macnemo.png',
					'class' => "c-bbcode-external-image",
					'style' => "float: left;",
					'alt' => "",
				))
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// float right
			$input = '[img=right]http://localhost/img/macnemo.png[/img]';
			$expected = array(
				array('img' => array(
					'src' => 'http://localhost/img/macnemo.png',
					'class' => "c-bbcode-external-image",
					'style' => "float: right;",
					'alt' => "",
				))
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testImageNestedInExternalLink() {
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
						'class' => 'c-bbcode-external-image',
						'alt' => '',
					]
				],
				'/a'
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testInternalImage() {
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

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
			$FileUploader = $this->getMock('FileUploaderHelper', ['image', 'reset']);
			$FileUploader->expects($this->atLeastOnce())
				->method('image')
				->will($this->returnValueMap($map));
			$this->_Bbcode->FileUpload = $FileUploader;

			// internal image
			$input = '[upload]test.png[/upload]';
			$expected = [
				['div' => ['class' => 'c-bbcode-upload']],
				['img' => ['src' => 'test.png']],
				'/div',
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// internal image with attributes
			$input = '[upload width=50 height=60]test.png[/upload]';
			$expected = array(
				['div' => ['class' => 'c-bbcode-upload']],
				['img' =>
					[
						'src' => 'test.png',
						'width' => '50',
						'height' => '60',
						'alt' => '',
					]
				],
				'/div',
			);
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// internal image legacy [img#] tag
			$input = '[img#]test.png[/img]';
			$expected = [
				['div' => [
					'class' => 'c-bbcode-upload'
				]],
				[
					'img' => [
						'src' => 'test.png',
					]
				],
				'/div',
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// nested image does not have [domain.info]
			$input = '[url=http://heise.de][upload]test.png[/upload][/url]';
			$expected = "/c-bbcode-link-dinfo/";
			$result = $this->_Bbcode->parse($input);
			$this->assertNotRegExp($expected, $result);

			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testSmiliesHtmlEntities() {
			$input = h('ü :-) ü');
			$expected = [
				'ü ',
				'img' => [
					'src' => $this->_Bbcode->webroot('img/smilies/smile_image.svg'),
					'alt' => ':-)',
					'title' => 'Smile',
					'class' => 'saito-smiley-image',
				],
				' ü'
			];
			$result = $this->_Bbcode->parse($input, array('cache' => false));
			$this->assertTags($result, $expected);
		}

		public function testSmiliesNoSmiliesInCodeTag() {
			$input = '[code text]:)[/code]';
			$needle = '<img';
			$result = $this->_Bbcode->parse($input, array('cache' => false));
			$this->assertNotContains($needle, $result);
		}

		public function testSmiliesPixelImage() {
			$input = ';)';
			$expected = [
				'img' => [
					'src' => $this->_Bbcode->webroot(
							'img/smilies/wink.png'
						),
					'alt' => ';)',
					'class' => 'saito-smiley-image',
					'title' => 'Wink'
				]
			];
			$result = $this->_Bbcode->parse($input, ['cache' => false]);
			$this->assertTags($result, $expected);
		}

		public function testSmiliesVectorFont() {
			$input = '[_]P';
			$expected = [
				'i' => [
					'class' => 'saito-smiley-font saito-smiley-coffee',
					'title' => 'Coffee'
				]
			];
			$result = $this->_Bbcode->parse($input, ['cache' => false]);
			$this->assertTags($result, $expected);
		}

		public function testCodeNestedTags() {
			$input = '[code][b]text[b][/code]';
			$expected = [
				[
					'div' => ['class' => 'c-bbcode-code-wrapper']
				],
				'preg:/.*?\[b\]text\[b\].*/',
			];
			$result = $this->_Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testCodeWhitespace() {
			$input = "[code]\ntest\n[/code]";
			$expected = "/>test</";
			$result = $this->_Bbcode->parse($input);
			$this->assertRegExp($expected, $result);
		}

		public function testCodeSimple() {
			$input = '[code]text[/code]';
			$result = $this->_Bbcode->parse($input);
			$expected = 'lang="text"';
			$this->assertContains($expected, $result);
		}

		public function testCodeLangAttribute() {
			$input = '[code=php]text[/code]';
			$result = $this->_Bbcode->parse($input);
			$expected = 'lang="php"';
			$this->assertContains($expected, $result);
		}

		/**
		 * tests that citation marks are not replaced in code-blocks
		 */
		public function testCodeNoCitationMark() {
			// [code]<citation mark>[/code] should not be cited
			$input = h(
				"[code]\n" . $this->_Bbcode->settings['quote_symbol'] . "\n[/code]"
			);
			$expected = '`span class=.*?c-bbcode-citation`';
			$result = $this->_Bbcode->parse($input);
			$this->assertNotRegExp($expected, $result);
		}

		public function testCodeDetaginize() {
			$input = '[code bash]pre http://example.com post[/code]';
			$result = $this->_Bbcode->parse($input);
			$this->assertNotContains('autoLink', $result);
		}

		public function testQuote() {
			$_qs = h($this->_Bbcode->settings['quote_symbol']);
			$input = $_qs . ' fo [b]test[/b] ba';
			$result = $this->_Bbcode->parse($input);
			$expected = [
				'span' => ['class' => 'c-bbcode-citation'],
				$_qs . ' fo ',
				'strong' => [],
				'test',
				'/strong',
				' ba',
				'/span'
			];
			$this->assertTags($result, $expected);
		}

		public function testHtml5Video() {
			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			//* @td write video tests
			$url = 'http://example.com/audio.mp4';
			$input = "[video]{$url}[/video]";
			$result = $this->_Bbcode->parse($input);
			$expected = array(
				'video' => array('src' => $url, 'controls' => 'controls',
					'x-webkit-airplay' => 'allow'),
			);
			$this->assertTags($result, $expected);

			//* test autoconversion for audio files
			$html5AudioExtensions = array('m4a', 'ogg', 'mp3', 'wav', 'opus');
			foreach ($html5AudioExtensions as $extension) {
				$url = 'http://example.com/audio.' . $extension;
				$input = "[video]{$url}[/video]";
				$result = $this->_Bbcode->parse($input);
				$expected = array(
					'audio' => array('src' => $url, 'controls' => 'controls'),
				);
				$this->assertTags($result, $expected);
			}

			//* teardown
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testHr() {
			$input = '[hr][hr]';
			$expected = '<hr class="c-bbcode-hr"><hr class="c-bbcode-hr">';
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($result, $expected);
		}

		public function testHrShort() {
			$input = '[---][---]';
			$expected = '<hr class="c-bbcode-hr"><hr class="c-bbcode-hr">';
			$result = $this->_Bbcode->parse($input);
			$this->assertEquals($result, $expected);
		}

		public function testEmbedlyDisabled() {
			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			$embedlyEnabled = Configure::read('Saito.Settings.embedly_enabled');
			$embedlyKey = Configure::read('Saito.Settings.embedly_key');

			$observer = $this->getMock('Embedly', array('setApiKey', 'embedly'));
			$observer->expects($this->never())
				->method('setApiKey');
			$this->_Bbcode->Embedly = $observer;
			$input = '[embed]foo[/embed]';
			$this->_Bbcode->parse($input);

			//* teardown
			Configure::write('Saito.Settings.embedly_enabled', $embedlyEnabled);
			Configure::write('Saito.Settings.embedly_key', $embedlyKey);
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testEmbedlyEnabled() {
			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			$embedlyEnabled = Configure::read('Saito.Settings.embedly_enabled');
			$embedlyKey = Configure::read('Saito.Settings.embedly_key');

			Configure::write('Saito.Settings.embedly_enabled', true);
			Configure::write('Saito.Settings.embedly_key', 'abc123');

			$observer = $this->getMock('Embedly', array('setApiKey', 'embedly'));
			$observer->expects($this->once())
				->method('setApiKey')
				->with($this->equalTo('abc123'));
			$observer->expects($this->once())
				->method('embedly')
				->with($this->equalTo('foo'));
			$this->_Bbcode->Embedly = $observer;
			$input = '[embed]foo[/embed]';
			$this->_Bbcode->parse($input);

			//* teardown
			Configure::write('Saito.Settings.embedly_enabled', $embedlyEnabled);
			Configure::write('Saito.Settings.embedly_key', $embedlyKey);
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testHtml5Audio() {
			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			//* simple test
			$url = 'http://example.com/audio3.m4a';
			$input = "[audio]{$url}[/audio]";
			$result = $this->_Bbcode->parse($input);
			$expected = array(
					'audio' => array( 'src' => $url, 'controls' => 'controls' ),
			);
			$this->assertTags($result, $expected);

			//* teardown
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testCiteText() {
			$input = "";
			$result = $this->_Bbcode->citeText($input);
			$expected = "";
			$this->assertEquals($result, $expected);

			$input = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789";
			$result = $this->_Bbcode->citeText($input);
			$expected = "» 123456789 123456789 123456789 123456789 123456789 123456789 123456789\n» 123456789\n";
			$this->assertEquals($result, $expected);
		}

		/*		 * ******************** Setup ********************** */

		public function setUp() {
			Cache::clear();

			Configure::write('Asset.timestamp', false);

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

			$this->smilies = Configure::read('Saito.Settings.smilies');
			Configure::write('Saito.Settings.smilies', true);

			if ( isset($_SERVER['SERVER_NAME']) ) {
				$this->server_name = $_SERVER['SERVER_NAME'];
			} else {
				$this->server_name = false;
			}

			if ( isset($_SERVER['SERVER_PORT']) ) {
				$this->server_port = $_SERVER['SERVER_PORT'];
			} else {
				$this->server_port = false;
			}

			$this->asset_timestamp = Configure::read('Asset.timestamp');

			$this->text_word_maxlength = Configure::read('Saito.Settings.text_word_maxlength');
			Configure::write('Saito.Settings.text_word_maxlength', 10000);

			$this->autolink = Configure::read('Saito.Settings.autolink');
			Configure::write('Saito.Settings.autolink', true);

			$_SERVER['SERVER_NAME'] = 'macnemo.de';
			$_SERVER['SERVER_PORT'] = '80';

			parent::setUp();
			$Request = new CakeRequest('/');
			$Controller = new Controller($Request);
			$View = new View($Controller);
			$BbcodeUserlist = new BbcodeUserlistArray();
			$BbcodeUserlist->set(['Alice', 'Bobby Junior', 'Dr. No']);

			$settings = [
				'quote_symbol' => '»',
				'hashBaseUrl' => '/hash/',
				'atBaseUrl' => '/at/',
				'smiliesData' => $smiliesFixture,
				'UserList' => $BbcodeUserlist
			];

			$this->_Bbcode = new BbcodeHelper($View);
			$this->_Bbcode->settings = $settings;
			$this->_Bbcode->beforeRender(null);
		}

		public function tearDown() {
			parent::tearDown();
			if ( $this->server_name ) {
				$_SERVER['SERVER_NAME'] = $this->server_name;
			}

			if ($this->server_name) {
				$_SERVER['SERVER_PORT'] = $this->server_port;
			}

			Configure::write('Asset.timestamp', $this->asset_timestamp);

			Configure::write('Saito.Settings.text_word_maxlength',
					$this->text_word_maxlength);

			Configure::write('Saito.Settings.autolink', $this->autolink);

			Configure::write('Saito.Settings.smilies', $this->smilies);

			Cache::clear();
			ClassRegistry::flush();
			unset($this->_Bbcode);
		}

	}

