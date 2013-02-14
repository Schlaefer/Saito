<?php

	/* Bbcode Test cases generated on: 2010-08-02 07:08:44 : 1280727824 */
	App::import('Lib', 'Stopwatch.Stopwatch');
	App::import('Helper',
			array(
			'FileUpload.FileUpload',
			'CakephpGeshi.Geshi',
	));
	App::import('Helper', 'MailObfuscator.MailObfuscator');

	App::uses('Sanitize', 'Utility');
	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('BbcodeHelper', 'View/Helper');
	App::uses('HtmlHelper', 'View/Helper');
	App::uses('CakeRequest', 'Network');

	class BbcodeHelperTest extends CakeTestCase {

		private $Bbcode = null;

		/**
		 * Preserves $GLOBALS vars through PHPUnit test runs
		 *
		 * @see http://www.phpunit.de/manual/3.6/en/fixtures.html#fixtures.global-state
		 * @var array
		 */
		protected $backupGlobalsBlacklist = array(
			/*
			 * $GLOBALS['__STRINGPARSER_NODE_ID' is set in stringparser.class.php
			 * and must not cleared out 
			 */
			'__STRINGPARSER_NODE_ID'
		);

		public function testSimpleTextDecorations() {

			//* bold
			$input = '[b]bold[/b]';
			$expected = array( 'strong' => array( ), 'bold', '/strong' );
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* emphasis
			$input = '[i]italic[/i]';
			$expected = array( 'em' => array( ), 'italic', '/em' );
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* underline
			$input = '[u]text[/u]';
			$expected = array( 'span' => array( 'class' => 'c_bbc_underline' ), 'text', '/span' );
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* strike
			$input = '[strike]text[/strike]';
			$expected = array( 'del' => array( ), 'text', '/del' );
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testList() {
			$input = "[list]\n[*]fooo\n[*]bar\n[/list]";
			$expected = array(
					array( 'ul' => array( 'class' => 'c_bbc_ul' ) ),
					array( 'li' => array( 'class' => 'c_bbc_li' ) ),
					'fooo',
					array( 'br' => array( ) ),
					'/li',
					array( 'li' => array( 'class' => 'c_bbc_li' ) ),
					'bar',
					'/li',
					'/ul'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testLink() {
				$https = env('HTTPS');
				$_SERVER['HTTPS'] = false;

			$input = '[url=http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&item=250678480561&ssPageName=ADME:X:RTQ:DE:1123]test[/url]';
			$expected = "<a href='http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&item=250678480561&ssPageName=ADME:X:RTQ:DE:1123' rel='external' target='_blank'>test</a> <span class='c_bbc_link-dinfo'>[ebay.de]</span>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

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
			$result = $this->Bbcode->parse($input);
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
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			$input = '[url]heise.de/foobar[/url]';
			$expected = "<a href='http://heise.de/foobar' rel='external' target='_blank'>heise.de/foobar</a>";
			$expected = array(
					'a' => array(
							'href' => 'http://heise.de/foobar',
							'rel' => 'external',
							'target' => '_blank'
					),
					'heise.de/foobar',
					'/a'
			);
			$result = $this->Bbcode->parse($input);
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
					'span' => array( 'class' => 'c_bbc_link-dinfo' ), '[heise.de]', '/span'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			$input = '[url=heise.de/bar]foobar[/url]';
			$expected = "<a href='http://heise.de/bar' rel='external' target='_blank'>foobar</a> <span class='c_bbc_link-dinfo'>[heise.de]</span>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// masked link: strip subdomain from domain info
			$input = '[url=www.heise.de/bar]foobar[/url]';
			$expected = "<a href='http://www.heise.de/bar' rel='external' target='_blank'>foobar</a> <span class='c_bbc_link-dinfo'>[heise.de]</span>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

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
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			$input = '[url]heise.de/bar[/url]';
			$expected = array(
					'a' => array(
							'href' => 'http://heise.de/bar',
							'rel' => 'external',
							'target' => '_blank',
					),
					'heise.de/bar',
					'/a',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			/**
			 * local server
			 */
			$input = '[url=http://macnemo.de/foobar]foobar[/url]';
			$expected = "<a href='http://macnemo.de/foobar'>foobar</a> <span class='c_bbc_link-dinfo'>[macnemo.de]</span>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			$input = '[url]/foobar[/url]';
			$expected = array(
					'a' => array(
							'href' => 'http://macnemo.de/foobar',
					),
					'preg:/\/foobar/',
					'/a',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);


			// test lokaler server with absolute url
			$input = '[url=/foobar]foobar[/url]';
			$expected = "<a href='http://macnemo.de/foobar'>foobar</a> <span class='c_bbc_link-dinfo'>[macnemo.de]</span>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test 'http://' only
			$input = '[url=http://]foobar[/url]';
			$expected = "<a href='http://'>foobar</a> <span class='c_bbc_link-dinfo'>[]</span>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

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
					'span' => array( 'class' => 'c_bbc_link-dinfo' ), '[heise.co.uk]', '/span'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			$_SERVER['HTTPS'] = $https;
		}

		public function testLinkHttps() {
			$https = env('HTTPS');
			$_SERVER['HTTPS'] = true;

			$input = '[url]/foobar[/url]';
			$expected = array(
								'a' => array(
								'href' => 'https://macnemo.de/foobar',
					),
								'preg:/\/foobar/',
								'/a',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			$_SERVER['HTTPS'] = $https;
		}

		public function testEmail() {
			// mailto:
			$input = '[email]mailto:mail@tosomeone.com[/email]';
			$expected = "<a href='mailto:mail@tosomeone.com'>mailto:mail@tosomeone.com</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// mailto: mask
			$input = '[email=mailto:mail@tosomeone.com]Mail[/email]';
			$expected = "<a href='mailto:mail@tosomeone.com'>Mail</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// no mailto:
			$input = '[email]mail@tosomeone.com[/email]';
			$expected = "<a href='mailto:mail@tosomeone.com'>mail@tosomeone.com</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// no mailto: mask
			$input = '[email=mail@tosomeone.com]Mail[/email]';
			$expected = "<a href='mailto:mail@tosomeone.com'>Mail</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			/**
			 * Tests for Rot13
			 *
			  // mail
			  $input 		= '[email]mailto:mail@tosomeone.com[/email]';
			  $expected	=	'<span id=\'moh_0\'></span><script type="text/javascript">Rot13.write(\'<n uers="znvygb:znvy@gbfbzrbar.pbz">znvygb:znvy@gbfbzrbar.pbz</n>\', \'moh_0\');</script>';
			  $result		= $this->Bbcode->parse($input);
			  $this->assertIdentical($expected, $result);

			  // mail mask
			  $input 		= '[email=mailto:mail@tosomeone.com]Mail[/email]';
			  $expected	=	'<span id=\'moh_1\'></span><script type="text/javascript">Rot13.write(\'<n uers="znvygb:znvy@gbfbzrbar.pbz">Znvy</n>\', \'moh_1\');</script>';
			  $result		= $this->Bbcode->parse($input);
			  $this->assertIdentical($expected, $result);

			  // mail without mailto:
			  $input 		= '[email]mail@tosomeone.com[/email]';
			  $expected	=	'<span id=\'moh_2\'></span><script type="text/javascript">Rot13.write(\'<n uers="znvygb:znvy@gbfbzrbar.pbz">znvy@gbfbzrbar.pbz</n>\', \'moh_2\');</script>';
			  $result		= $this->Bbcode->parse($input);
			  $this->assertIdentical($expected, $result);

			  // mail mask without mailto:
			  $input 		= '[email=mail@tosomeone.com]Mail[/email]';
			  $expected	=	'<span id=\'moh_3\'></span><script type="text/javascript">Rot13.write(\'<n uers="znvygb:znvy@gbfbzrbar.pbz">Znvy</n>\', \'moh_3\');</script>';
			  $result		= $this->Bbcode->parse($input);
			  $this->assertIdentical($expected, $result);
			 */
		}

		public function testAutoLink() {
			$input = 'http://heise.de/foobar';
			$expected = "<a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// autolink surrounded by text
			$input = 'some http://heise.de/foobar text';
			$expected = "some <a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a> text";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// autolink without http:// prefix
			$input = 'some www.heise.de/foobar text';
			$expected = array(
					'some ',
					'a' => array(
							'href' => 'http://www.heise.de/foobar',
							'rel' => 'external',
							'target' => '_blank',
					),
					'www.heise.de/foobar',
					'/a',
					' text'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// email autolink
			$input = 'text mail@tosomeone.com test';
			$expected = "text <a href='mailto:mail@tosomeone.com'>mail@tosomeone.com</a> test";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);
			/** rot 13
			  $input 		= 'text mail@tosomeone.com test';
			  $expected	=	'text <span id=\'moh_4\'></span><script type="text/javascript">Rot13.write(\'<n uers="znvygb:znvy@gbfbzrbar.pbz">znvy@gbfbzrbar.pbz</n>\', \'moh_4\');</script> test';
			  $result		= $this->Bbcode->parse($input);
			  $this->assertIdentical($expected, $result);
			 */
		}

		public function testShortenLink() {
			$length_max = 15;

			$text_word_maxlenghth = Configure::read('Saito.Settings.text_word_maxlength');
			Configure::write('Saito.Settings.text_word_maxlength', $length_max);

			$input = '[url]http://this/url/is/32/chars/long[/url]';
			$expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			$input = 'http://this/url/is/32/chars/long';
			$expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			Configure::write('Saito.Settings.text_word_maxlength', $text_word_maxlenghth);
		}

		public function testIframe() {
			$bbcode_img = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			$video_domains = Configure::read('Saito.Settings.video_domains_allowed');
			Configure::write('Saito.Settings.video_domains_allowed', 'youtube | vimeo');

			//* test allowed domain
			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtube.com/embed/HdoW3t_WorU ' .
					'frameborder=0][/iframe]';
			$expected = array(
					array( 'iframe' => array(
									'src' => 'http://www.youtube.com/embed/HdoW3t_WorU?&wmode=Opaque',
									'height' => '349',
									'width' => '560',
									'frameborder' => '0',
					) ),
					'/iframe',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* test forbidden domains
			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
					'frameborder=0][/iframe]';
			$expected = '/src/i';
			$result = $this->Bbcode->parse($input);
			$this->assertNoPattern($expected, $result);

      /*
       * test if all domains are allowed
       */
			Configure::write('Saito.Settings.video_domains_allowed', '*');

      $refObject   = new ReflectionObject($this->Bbcode);
      $refProperty = $refObject->getProperty('_allowedVideoDomains');
      $refProperty->setAccessible(true);
      $refProperty->setValue('_allowedVideoDomains', null);

			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
					'][/iframe]';
			$expected = 'src="http://www.youtubescam.com/embed/HdoW3t_WorU';
			$result = $this->Bbcode->parse($input);
			$this->assertContains($expected, $result);

      /*
       * test if no domains are allowed
       */
			Configure::write('Saito.Settings.video_domains_allowed', '');

      $refObject   = new ReflectionObject($this->Bbcode);
      $refProperty = $refObject->getProperty('_allowedVideoDomains');
      $refProperty->setAccessible(true);
      $refProperty->setValue('_allowedVideoDomains', null);

			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
					'][/iframe]';
			$expected = '/src/i';
			$result = $this->Bbcode->parse($input);
			$this->assertNoPattern($expected, $result);
		}

		public function testExternalImage() {
			$bbcode_img = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			// test for standard URIs
			$input = '[img]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c_bbc_external-image" width="auto" height="auto" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test for URIs without protocol
			$input = '[img]/somewhere/macnemo.png[/img]';
			$expected = '<img src="http://macnemo.de/somewhere/macnemo.png" class="c_bbc_external-image" width="auto" height="auto" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			$input = '[img]heise.de/img/macnemo.png[/img]';
			$expected = '<img src="http://heise.de/img/macnemo.png" class="c_bbc_external-image" width="auto" height="auto" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test scaling with 1 parameter
			$input = '[img=50]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c_bbc_external-image" width="50" height="auto" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test scaling with 2 parameters
			$input = '[img=50x100]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c_bbc_external-image" width="50" height="100" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// float left
			$input = '[img=left]http://localhost/img/macnemo.png[/img]';
			$expected = array(
					array( 'img' => array(
									'src' => 'http://localhost/img/macnemo.png',
									'class' => "c_bbc_external-image",
									'style' => "float: left;",
									'width' => "auto",
									'height' => "auto",
									'alt' => "",
					) )
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// float right
			$input = '[img=right]http://localhost/img/macnemo.png[/img]';
			$expected = array(
					array( 'img' => array(
									'src' => 'http://localhost/img/macnemo.png',
									'class' => "c_bbc_external-image",
									'style' => "float: right;",
									'width' => "auto",
									'height' => "auto",
									'alt' => "",
					) )
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// image nested in external link
			$input = '[url=http://heise.de][img]http://heise.de/img.png[/img][/url]';
			$expected = "<a href='http://heise.de' rel='external' target='_blank'><img src=\"http://heise.de/img.png\" class=\"external_image\" style=\"\" width=\"auto\" height=\"auto\" alt=\"\" /></a>";
			$expected = array(
					array( 'a' => array(
									'href' => 'http://heise.de',
									'rel' => 'external',
									'target' => '_blank',
					) ),
					array( 'img' => array(
									'src' => 'http://heise.de/img.png',
									'class' => 'c_bbc_external-image',
									'width' => 'auto',
									'height' => 'auto',
									'alt' => '',
					) ),
					'/a'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			Configure::write('Saito.Settings.bbcode_img', $bbcode_img);
		}

		public function testInternalImage() {
			  $bbcode_img = Configure::read('Saito.Settings.bbcode_img');
			  Configure::write('Saito.Settings.bbcode_img', true);

         // Create a map of arguments to return values.
        $map = array(
          array( 'test.png', '<img src="test.png" />' ),
          array(
              'test.png',
              array(
                  'autoResize'      => FALSE,
                  'resizeThumbOnly' => FALSE,
                  'width'           => '50',
                  'height'          => '60',
              ),
              '<img src="test.png" width="50" height="60" alt="">'
          )
        );
				$FileUploader = $this->getMock('FileUploaderHelper', array( 'image', 'reset' ));
				$FileUploader->expects($this->atLeastOnce())
					->method('image')
					->will($this->returnValueMap($map));
				$this->Bbcode->FileUpload =  $FileUploader;

        // internal image
			  $input 		= '[upload]test.png[/upload]';
			  $expected = array(
			  array( 'div' => array('class' => 'c_bbc_upload')),
			  array( 'img' => array(
			  'src'	=> 'test.png',
			  )),
			  '/div',
			  );
			  $result		= $this->Bbcode->parse($input);
			  $this->assertTags($result, $expected);

        // internal image with attributes
			  $input = '[upload width=50 height=60]test.png[/upload]';
        $expected = array(
            array( 'div' => array( 'class' => 'c_bbc_upload' ) ),
            array( 'img' => array(
                    'src'     => 'test.png',
                    'width'   => '50',
                    'height'  => '60',
                    'alt'     => '',
            ),
                ),
            '/div',
        );
        $result = $this->Bbcode->parse($input);
        $this->assertTags($result, $expected);

        // internal image legacy [img#] tag
			  $input 		= '[img#]test.png[/img]';
			  $expected = array(
			  array( 'div' => array(
            'class' => 'c_bbc_upload'
            ) ),
          array( 'img' => array(
                  'src' => 'test.png',
          ) ),
          '/div',
        );
			  $result		= $this->Bbcode->parse($input);
			  $this->assertTags($result, $expected);

			  // nested image does not have [domain.info]
			  $input 		= '[url=http://heise.de][upload]test.png[/upload][/url]';
			  $expected	=	"/c_bbc_link-dinfo/";
			  $result		= $this->Bbcode->parse($input);
			  $this->assertNoPattern($expected, $result);

			  Configure::write('Saito.Settings.bbcode_img', $bbcode_img);
		}

		public function testSmilies() {

			$input = ';)';
			$expected = array( 'img' => array( 'src' => $this->Bbcode->webroot('img/smilies/wink.png'), 'alt' => ';)', 'title' => 'Wink' ) );
			$result = $this->Bbcode->parse($input, array( 'cache' => false ));
			$this->assertTags($result, $expected);

			// test html entities
			$input = Sanitize::html('ü :-) ü');
			$expected = array( '&uuml; ', 'img' => array( 'src' => $this->Bbcode->webroot('img/smilies/smile_image.png'), 'alt' => ':-)', 'title' => 'Smile' ), ' &uuml;' );
			$result = $this->Bbcode->parse($input, array( 'cache' => false ));
			$this->assertTags($result, $expected);

			// test html entities
			$input = Sanitize::html('foo …) bar €) batz');
			$expected = 'foo &hellip;) bar &euro;) batz';
			$result = $this->Bbcode->parse($input, array( 'cache' => FALSE ));
			$this->assertIdentical($expected, $result);
		}

		public function testCode() {

			//* test whitespace
			$input = "[code]\ntest\n[/code]";
			$expected = "/>test</";
			$result = $this->Bbcode->parse($input);
			$this->assertPattern($expected, $result);

			//* test escaping of [bbcode]
			$input = '[code][b]text[b][/code]';
			$expected = array(
					array( 'div' => array( 'class' => 'c_bbc_code-wrapper' ) ),
					'preg:/.*?\[b\]text\[b\].*/',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// [code]<citation mark>[/code] should not be cited
			$input = Sanitize::html("[code]\n" . Configure::read('Saito.Settings.quote_symbol') . "\n[/code]");
			$expected = '`span class=.*?c_bbc_citation`';
			$result = $this->Bbcode->parse($input);
			$this->assertNoPattern($expected, $result);

      /*
       * test setting a sourcecode type
       */
			$input = '[code php]text[/code]';
			$result = $this->Bbcode->parse($input);
			$expected = 'lang="php"';
      $this->assertContains($expected, $result);
		}

		public function testMarkiereZitat() {

			$input = Sanitize::html("» test");
			$result = $this->Bbcode->parse($input);
			$expected = array(
					'span' => array( 'class' => 'c_bbc_citation' ),
					'&raquo; test',
					'/span',
			);
			$this->assertTags($result, $expected);
		}

		public function testHtml5Video() {
			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			//* @td write video tests
			$url = 'http://example.com/audio.mp4';
			$input = "[video]{$url}[/video]";
			$result = $this->Bbcode->parse($input);
			$expected = array(
					'video' => array( 'src' => $url, 'controls' => 'controls', 'x-webkit-airplay' => 'allow' ),
			);
			$this->assertTags($result, $expected);

			//* test autoconversion for audio files
			$html5AudioExtensions = array( 'm4a', 'ogg', 'mp3', 'wav', 'opus' );
			foreach ( $html5AudioExtensions as $extension ) {
				$url = 'http://example.com/audio.' . $extension;
				$input = "[video]{$url}[/video]";
				$result = $this->Bbcode->parse($input);
				$expected = array(
						'audio' => array( 'src' => $url, 'controls' => 'controls' ),
				);
				$this->assertTags($result, $expected);
			}

			//* teardown
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

    public function testEmbedly() {

			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

      $embedly_enabled = Configure::read('Saito.Settings.embedly_enabled');
      $embedly_key = Configure::read('Saito.Settings.embedly_key');

      // Embedly is disabled
      $observer = $this->getMock('Embedly', array( 'setApiKey', 'embedly' ));
      $observer->expects($this->never())
          ->method('setApiKey');
      $this->Bbcode->Embedly = $observer;
      $input = '[embed]foo[/embed]';
      $result = $this->Bbcode->parse($input);

      // Embedly is enabled
      Configure::write('Saito.Settings.embedly_enabled', TRUE);
      Configure::write('Saito.Settings.embedly_key', 'abc123');

      $observer = $this->getMock('Embedly', array( 'setApiKey', 'embedly' ));
      $observer->expects($this->once())
          ->method('setApiKey')
          ->with($this->equalTo('abc123'));
      $observer->expects($this->once())
          ->method('embedly')
          ->with($this->equalTo('foo'));
      $this->Bbcode->Embedly = $observer;
      $result = $this->Bbcode->parse($input);

			//* teardown
      Configure::write('Saito.Settings.embedly_enabled', $embedly_enabled);
      Configure::write('Saito.Settings.embedly_key', $embedly_key);
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
    }

    public function testHtml5Audio() {

			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			//* simple test
			$url = 'http://example.com/audio3.m4a';
			$input = "[audio]{$url}[/audio]";
			$result = $this->Bbcode->parse($input);
			$expected = array(
					'audio' => array( 'src' => $url, 'controls' => 'controls' ),
			);
			$this->assertTags($result, $expected);

			//* teardown
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testCiteText() {

			$input = "";
			$result = $this->Bbcode->citeText($input);
			$expected = "";
			$this->assertEqual($result, $expected);

			$input = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789";
			$result = $this->Bbcode->citeText($input);
			$expected = "» 123456789 123456789 123456789 123456789 123456789 123456789 123456789\n» 123456789\n";
			$this->assertEqual($result, $expected);
		}

		/*		 * ******************** Setup ********************** */

		public function setUp() {
			Cache::clear();

			Configure::write('Asset.timestamp', false);

			$smilies_fixture = array(
					array(
							'order' => 1,
							'icon' => 'wink.png',
							'image' => 'wink.png',
							'title' => 'Wink',
							'code' => ';)',
					),
					array(
							'order' => 2,
							'icon' => 'smile_icon.png',
							'image' => 'smile_image.png',
							'title' => 'Smile',
							'code' => ':-)',
					),
					array(
							'order' => 2,
							'icon' => 'smile_icon.png',
							'image' => 'smile_image.png',
							'title' => 'Smile',
							'code' => ';-)',
					),
			);

			$this->smilies_all = Configure::read('Saito.Smilies.smilies_all');
			Configure::write('Saito.Smilies.smilies_all', $smilies_fixture);

			$this->smilies = Configure::read('Saito.Settings.smilies');
			Configure::write('Saito.Settings.smilies', true);

			Configure::write("Saito.Smilies.smilies_all_html", false);

			if ( isset($_SERVER['SERVER_NAME']) ) {
				$this->server_name = $_SERVER['SERVER_NAME'];
			} else {
				$this->server_name = FALSE;
			}

			Configure::write('Saito.Settings.quote_symbol', '»');

			$this->asset_timestamp = Configure::read('Asset.timestamp');

			$this->text_word_maxlength = Configure::read('Saito.Settings.text_word_maxlength');
			Configure::write('Saito.Settings.text_word_maxlength', 10000);

			$this->autolink = Configure::read('Saito.Settings.autolink');
			Configure::write('Saito.Settings.autolink', true);

			$_SERVER['SERVER_NAME'] = 'macnemo.de';

			parent::setUp();
			$Request = new CakeRequest('/');
			$Controller = new Controller($Request);
			$View = new View($Controller);

			$this->Bbcode = new BbcodeHelper($View);
			$this->Bbcode->beforeRender(null);
		}

		public function tearDown() {
			parent::tearDown();
			if ( $this->server_name ) {
				$_SERVER['SERVER_NAME'] = $this->server_name;
			}

			Configure::write('Asset.timestamp', $this->asset_timestamp);

			Configure::write('Saito.Settings.text_word_maxlength',
					$this->text_word_maxlength);

			Configure::write('Saito.Settings.autolink', $this->autolink);

			Configure::write('Saito.Settings.smilies', $this->smilies);
			Configure::write('Saito.Settings.smilies_all', $this->smilies_all);

			Cache::clear();
			ClassRegistry::flush();
			unset($this->Bbcode);
		}

	}

?>